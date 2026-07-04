"""
main.py
───────
WHY:    The entry point — wires everything together and exposes HTTP endpoints.

WHAT:   - Defines the FastAPI app with a lifespan that starts/stops
          the APScheduler background job.
        - Exposes three endpoints:
            POST /api/chat    — the main chatbot endpoint
            POST /api/refresh — manually trigger a knowledge base refresh
            GET  /api/status  — health check + Qdrant stats
            GET  /           — serve the chatbot UI (index.html)
        - Handles CORS so the Laravel app can call this API.

HOW:    Uses FastAPI's async request handling for fast concurrent responses.
        The scheduler runs in a separate background thread.
"""

import logging
import os
import time
from contextlib import asynccontextmanager
from typing import List, Optional

from fastapi import FastAPI, HTTPException, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import HTMLResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from pydantic import BaseModel, Field
from slowapi import Limiter, _rate_limit_exceeded_handler
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded

from app import config
from app import vector_store
from app.retriever import retrieve
from app.chatbot import chat
from app.scheduler import start_scheduler, stop_scheduler, refresh_knowledge_base
from app import cache as answer_cache
from app.request_logger import log_request

# ── Rate limiter (30 requests/minute per IP) ─────────────────────────────────
limiter = Limiter(key_func=get_remote_address, default_limits=["30/minute"])


# ── Logging ───────────────────────────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s — %(message)s",
)
logger = logging.getLogger(__name__)


# ── Lifespan (startup / shutdown) ─────────────────────────────────────────────
@asynccontextmanager
async def lifespan(app: FastAPI):
    """
    Code BEFORE yield → runs at startup.
    Code AFTER yield  → runs at shutdown.
    """
    logger.info("═══ DIU AI Assistant starting up ═══")

    # Validate environment variables
    try:
        config.validate()
    except EnvironmentError as e:
        logger.error(f"[Startup] ❌ Configuration error: {e}")
        raise

    # Start the APScheduler background job
    start_scheduler()

    yield  # ← app runs here

    # Clean shutdown
    stop_scheduler()
    logger.info("═══ DIU AI Assistant shut down cleanly ═══")


# ── FastAPI app ───────────────────────────────────────────────────────────────
app = FastAPI(
    title="DIU AI Assistant",
    description="RAG-powered university chatbot for Daffodil International University",
    version="2.0.0",
    lifespan=lifespan,
    docs_url="/docs",
)

# ── Rate limiting ─────────────────────────────────────────────────────────────
app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)

# ── CORS ──────────────────────────────────────────────────────────────────────
# Allow the Laravel Campus Buddy app to call this API.
# In production, replace "*" with your actual Render domain.
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=False,
    allow_methods=["GET", "POST"],
    allow_headers=["*"],
)

# ── Static files & templates ──────────────────────────────────────────────────
_base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
_static_dir    = os.path.join(_base_dir, "static")
_templates_dir = os.path.join(_base_dir, "templates")

if os.path.exists(_static_dir):
    app.mount("/static", StaticFiles(directory=_static_dir), name="static")
templates = Jinja2Templates(directory=_templates_dir)


# ── Request / Response models ─────────────────────────────────────────────────
class ChatRequest(BaseModel):
    message: str = Field(..., min_length=1, max_length=1000,
                         description="The user's question")
    history: Optional[List[dict]] = Field(
        default=None,
        description="Optional list of previous messages [{role, content}]"
    )


class ChatResponse(BaseModel):
    answer:           str
    sources:          List[dict]
    found:            bool
    cached:           bool = False
    confidence_score: Optional[float] = None


class RefreshRequest(BaseModel):
    secret: str = Field(..., description="Must match REFRESH_SECRET in .env")
    force:  bool = Field(default=False, description="Force re-index all pages")


# ── Routes ────────────────────────────────────────────────────────────────────

@app.get("/", response_class=HTMLResponse)
async def home(request: Request):
    """Serve the chatbot UI (used for standalone testing)."""
    return templates.TemplateResponse("index.html", {
        "request": request,
        "university_name": config.UNIVERSITY_NAME,
    })


@app.post("/api/chat", response_model=ChatResponse)
@limiter.limit("30/minute")
async def api_chat(request: Request, body: ChatRequest):
    """
    Main chatbot endpoint.

    Flow:
        0. Check answer cache (instant return if hit)
        1. Retrieve top-5 relevant chunks from Qdrant.
        2. Generate answer using Groq LLM.
        3. Cache the result and log the request.
        4. Return answer + sources + confidence score.
    """
    t_start = time.time()
    error_msg: Optional[str] = None

    try:
        # Step 0: Check cache first
        cached = answer_cache.get(body.message)
        if cached:
            elapsed_ms = int((time.time() - t_start) * 1000)
            log_request(
                question=body.message, response_time_ms=elapsed_ms,
                chunks_retrieved=len(cached["sources"]),
                top_score=cached["sources"][0]["score"] if cached["sources"] else None,
                found=cached["found"], cached=True,
            )
            return ChatResponse(
                answer=cached["answer"],
                sources=cached["sources"],
                found=cached["found"],
                cached=True,
                confidence_score=round(cached["sources"][0]["score"], 3) if cached["sources"] else None,
            )

        # Step 1: Retrieve relevant context
        retrieval = retrieve(body.message)

        # Step 2: Generate answer using Groq
        answer = chat(
            question=body.message,
            context=retrieval["context"],
            sources=retrieval["sources"],
            history=body.history,
        )

        # Step 3: Cache for future identical questions
        answer_cache.set(body.message, answer, retrieval["sources"])

        top_score = retrieval["sources"][0]["score"] if retrieval["sources"] else None

        elapsed_ms = int((time.time() - t_start) * 1000)
        log_request(
            question=body.message, response_time_ms=elapsed_ms,
            chunks_retrieved=len(retrieval["sources"]),
            top_score=top_score, found=retrieval["found"], cached=False,
        )

        return ChatResponse(
            answer=answer,
            sources=retrieval["sources"],
            found=retrieval["found"],
            cached=False,
            confidence_score=round(top_score, 3) if top_score else None,
        )

    except Exception as e:
        error_msg = str(e)
        elapsed_ms = int((time.time() - t_start) * 1000)
        log_request(
            question=body.message, response_time_ms=elapsed_ms,
            chunks_retrieved=0, top_score=None, found=False, error=error_msg,
        )
        logger.exception(f"[API /chat] Error processing request: {e}")
        raise HTTPException(status_code=500, detail=f"Internal server error: {error_msg}")


@app.post("/api/refresh")
async def api_refresh(body: RefreshRequest):
    """
    Manually trigger a knowledge base refresh.
    Protected by a secret key to prevent unauthorized scraping.

    Use this after DIU updates their website if you don't want to wait
    for the next scheduled 24-hour refresh.
    """
    if config.REFRESH_SECRET and body.secret != config.REFRESH_SECRET:
        raise HTTPException(status_code=401, detail="Invalid secret key.")

    try:
        summary = refresh_knowledge_base(force=body.force)
        return summary
    except Exception as e:
        logger.error(f"[API /refresh] Error: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/status")
async def api_status():
    """
    Health check endpoint.
    Returns Qdrant collection stats and configuration info.
    """
    from app.scraper import API_SOURCES
    try:
        stats = vector_store.get_collection_stats()
    except Exception as e:
        stats = {"error": str(e)}

    return {
        "status":              "ok",
        "version":             "2.0.0",
        "model":               config.GROQ_MODEL,
        "embedding":           config.EMBEDDING_MODEL,
        "university":          config.UNIVERSITY_NAME,
        "qdrant":              stats,
        "refresh_every_hours": config.REFRESH_INTERVAL_HOURS,
        "api_sources":         len(API_SOURCES),
        "cache":               answer_cache.stats(),
    }


@app.get("/api/cache/stats")
async def api_cache_stats():
    """Cache statistics — how many questions are cached and TTL info."""
    return answer_cache.stats()


@app.delete("/api/cache")
async def api_cache_clear(secret: str = ""):
    """Clear the answer cache. Requires REFRESH_SECRET."""
    if config.REFRESH_SECRET and secret != config.REFRESH_SECRET:
        raise HTTPException(status_code=401, detail="Invalid secret key.")
    count = answer_cache.clear()
    return {"cleared": count, "message": f"Cleared {count} cached answers."}


# ── Waiver Calculator Proxy Endpoints ─────────────────────────────────────────
# We proxy requests to DIU's backend to avoid CORS issues in the browser.
_DIU_BACKEND = "https://webbackend.daffodilvarsity.edu.bd/api/v1/public"
_PROXY_HEADERS = {
    "User-Agent": "Mozilla/5.0 (compatible; DIU-Buddy/2.0)",
    "Accept": "application/json",
}

import requests as _requests


@app.get("/api/calculator/programs")
async def calculator_programs(tuition_category_id: int = 1, program_type_id: int = 1):
    """
    Proxy: Get list of programs for the waiver calculator dropdown.
    tuition_category_id: 1=Domestic, 2=International
    program_type_id:     1=Undergraduate, 2=Postgraduate
    """
    try:
        url = f"{_DIU_BACKEND}/tuition-fees"
        params = {
            "tuition_category_id": tuition_category_id,
            "program_type_id": program_type_id,
        }
        resp = _requests.get(url, params=params, headers=_PROXY_HEADERS, timeout=10)
        resp.raise_for_status()
        data = resp.json()
        tuitions = data.get("tuitions", [])
        # Return slim version for the dropdown
        programs = [
            {
                "id":           t["id"],
                "name":         t["program_name"],
                "duration":     t.get("program_duration", ""),
                "credit":       t.get("credit", ""),
                "total_fees":   t.get("total_fees", ""),
                "tuition_fees": t.get("tuition_fees", ""),
                "admission_fees": t.get("admission_fees", ""),
                "program_type": t.get("program_type", ""),
            }
            for t in tuitions
        ]
        return {"status": True, "programs": programs, "total": len(programs)}
    except Exception as e:
        logger.error(f"[Calculator] Programs proxy error: {e}")
        raise HTTPException(status_code=502, detail="Failed to fetch programs from DIU.")


@app.post("/api/calculator/calculate")
async def calculator_calculate(request: Request):
    """
    Proxy: Submit waiver calculation to DIU's backend.
    Accepts the same JSON body that DIU's /calculate endpoint expects.
    """
    try:
        body = await request.json()
        resp = _requests.post(
            f"{_DIU_BACKEND}/calculate",
            json=body,
            headers={**_PROXY_HEADERS, "Content-Type": "application/json"},
            timeout=10,
        )
        return resp.json()
    except Exception as e:
        logger.error(f"[Calculator] Calculate proxy error: {e}")
        raise HTTPException(status_code=502, detail="Failed to reach DIU calculation service.")

