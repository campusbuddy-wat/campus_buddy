"""
config.py
─────────
WHY:    Single source of truth for all environment variables and constants.
        Prevents hardcoded credentials anywhere in the codebase.
WHAT:   Reads the .env file and exposes typed settings as module-level
        variables that every other module imports from here.
HOW:    Uses python-dotenv to load .env, then reads each variable with
        os.getenv(). Raises clear errors at startup if required vars are missing.
"""

import os
from dotenv import load_dotenv

# Load .env file from the ai_service/ directory
load_dotenv()

# ── Groq ────────────────────────────────────────────────────────────────────
GROQ_API_KEY: str       = os.getenv("GROQ_API_KEY", "")
GROQ_MODEL: str         = os.getenv("GROQ_MODEL", "llama-3.1-8b-instant")
GROQ_MAX_TOKENS: int    = int(os.getenv("GROQ_MAX_TOKENS", "300"))
GROQ_TEMPERATURE: float = float(os.getenv("GROQ_TEMPERATURE", "0.1"))

# ── Qdrant ───────────────────────────────────────────────────────────────────
QDRANT_URL: str        = os.getenv("QDRANT_URL", "")
QDRANT_API_KEY: str    = os.getenv("QDRANT_API_KEY", "")
QDRANT_COLLECTION: str = os.getenv("QDRANT_COLLECTION", "diu_knowledge")

# ── Scheduler ────────────────────────────────────────────────────────────────
REFRESH_INTERVAL_HOURS: int = int(os.getenv("REFRESH_INTERVAL_HOURS", "24"))

# ── Crawler ───────────────────────────────────────────────────────────────────
# Entry point for the BFS web crawler
CRAWL_START_URL: str  = os.getenv("CRAWL_START_URL", "https://daffodilvarsity.edu.bd/")
# Maximum number of HTML pages to collect per crawl run
MAX_CRAWL_PAGES: int  = int(os.getenv("MAX_CRAWL_PAGES", "150"))
# Maximum link depth from the start URL (0 = start page only)
MAX_CRAWL_DEPTH: int  = int(os.getenv("MAX_CRAWL_DEPTH", "4"))

# ── Security ─────────────────────────────────────────────────────────────────
REFRESH_SECRET: str = os.getenv("REFRESH_SECRET", "")

# ── University ───────────────────────────────────────────────────────────────
UNIVERSITY_NAME: str  = os.getenv("UNIVERSITY_NAME", "Daffodil International University")
UNIVERSITY_SHORT: str = os.getenv("UNIVERSITY_SHORT", "DIU")

# ── Embedding model (runs locally — no API cost) ──────────────────────────────
# all-MiniLM-L6-v2 produces 384-dimension vectors, very fast, ~90MB download
EMBEDDING_MODEL: str  = "all-MiniLM-L6-v2"
EMBEDDING_DIM: int    = 384

# ── Chunking ────────────────────────────────────────────────────────────────
# Target: each chunk fits in 400 tokens so the top-3 context ≤ 1200 tokens
CHUNK_SIZE_TOKENS: int   = 400
CHUNK_OVERLAP_TOKENS: int = 50

# ── Retrieval ────────────────────────────────────────────────────────────────
TOP_K_RESULTS: int = 5

# ── Validate required variables at import time ───────────────────────────────
def validate():
    missing = []
    if not GROQ_API_KEY:
        missing.append("GROQ_API_KEY")
    if not QDRANT_URL:
        missing.append("QDRANT_URL")
    if not QDRANT_API_KEY:
        missing.append("QDRANT_API_KEY")
    if missing:
        raise EnvironmentError(
            f"Missing required environment variables: {', '.join(missing)}. "
            f"Copy .env.example to .env and fill in your values."
        )
