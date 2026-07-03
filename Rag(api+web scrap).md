# Campus Buddy AI — Architecture Upgrade Plan

## Honest Assessment: What You Already Have ✅

Before building anything new, it's important to see that your system **already implements most of the architecture** described. Here's the audit:

| Architecture Feature | Status | Notes |
|---|---|---|
| API-first data collection | ✅ Done | `API_SOURCES` in scraper.py — domestic + international tuition, admission |
| Web crawler (HTML fallback) | ✅ Done | `crawler.py` — BFS crawler for all HTML pages |
| Clean text extraction | ✅ Done | `_clean_html()`, `_flatten_tuition_fees()` |
| Chunking (300-500 tokens) | ✅ Done | `embedder.py` with sentence-boundary splitting + overlap |
| Embeddings | ✅ Done | `all-MiniLM-L6-v2` — local, free, fast |
| Vector DB | ✅ Done | Qdrant Cloud — semantic search |
| Similarity search + Top-K | ✅ Done | `retriever.py` — top-5 with deduplication |
| LLM answering | ✅ Done | Groq/llama-3.1-8b-instant |
| Source citation | ✅ Done | Every answer includes `🔗 Verify at: <url>` |
| Daily auto-update | ✅ Done | APScheduler every 24h |
| Incremental updates | ✅ Done | MD5 hash cache — skips unchanged pages |
| Conversation history | ✅ Done | Last 4 turns passed to LLM |
| Confidence score | ✅ Done | Qdrant score returned in `sources[].score` |
| Acronym expansion | ✅ Done | CSE→"Computer Science and Engineering" etc. |

> [!IMPORTANT]
> **You do NOT need to rewrite from scratch.** The foundation is solid.
> The architecture document describes exactly what you built. 
> Only **targeted additions** are needed.

---

## What's Missing — Gap Analysis

| Architecture Feature | Missing? | Priority |
|---|---|---|
| Rate limiting (30 req/min) | ❌ Not built | High |
| Request logging (question, response time, errors) | ❌ Not built | High |
| LLM retry on failure | ❌ Not built | High |
| Answer cache (same question → cached answer) | ❌ Not built | Medium |
| `category` + `last_updated` in chunk metadata | ❌ Not built | Medium |
| More DIU backend APIs (programs, faculty, clubs) | ❌ Not added | High |
| Playwright for JS-rendered pages | ⚠️ Optional | Low |
| Related questions | ❌ Not built | Low |
| Save data to JSON files | ❌ Not built | Low |

---

## My Suggestions (Highlighted Differences from the Document)

> [!NOTE]
> **Playwright — SKIP for now.** The document says "use Playwright for JS pages."
> The DIU main site IS a React SPA, but the important data (fees, programs, admission)
> comes from official JSON APIs — which we already use. Installing Playwright adds
> ~250MB of browser binaries, slows crawls, and complicates deployment on Render.
> Add it only if a key page has no API AND HTML scraping fails.

> [!NOTE]
> **"API Discovery" — keep it MANUAL.** The document shows automatic API inspection
> via the Network tab. In practice, auto-discovery is unreliable. Instead:
> - I'll add known DIU APIs (programs, departments, faculty) to `API_SOURCES`
> - The crawler continues as automatic fallback for everything else

> [!TIP]
> **Cache — use in-memory TTL dict**, not Redis. Redis adds infrastructure cost.
> A simple Python `{question_hash: (answer, timestamp)}` dict with 1-hour TTL
> is sufficient for a university chatbot and zero extra cost.

> [!TIP]
> **Rate limiting — use SlowAPI** (1 pip install). Provides per-IP limiting
> with exactly the same API as Flask-Limiter. No infrastructure needed.

---

## Implementation Plan — Phase by Phase

### Phase 1 — Critical (High Priority) — ~2 hours

These are must-haves for a production-ready system.

---

#### [MODIFY] `app/main.py`
- Add **SlowAPI rate limiter** — 30 requests/minute per IP
- Add **request logging middleware** — log question, response_time_ms, chunks_found, error
- Add **retry** on 503 errors (Groq down)
- Return `confidence_score` (already in sources, just expose it clearly)

#### [NEW] `app/request_logger.py`
Structured logger that writes one JSON line per request to `logs/requests.jsonl`:
```json
{
  "timestamp": "2026-07-03T12:00:00",
  "question": "What is CSE fee?",
  "response_time_ms": 1240,
  "chunks_retrieved": 5,
  "top_score": 0.87,
  "found": true,
  "error": null
}
```

#### [MODIFY] `app/chatbot.py`
- Add **tenacity retry** — 3 retries with 2s backoff on Groq failure
- On final failure return `"AI service temporarily unavailable. Please try again shortly."`

#### [MODIFY] `app/scraper.py` — Add more DIU APIs
Add known DIU backend APIs to `API_SOURCES`:
```python
# Programs list
{"page_id": "programs_api", "url": "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/programs", ...}
# Faculty/Departments
{"page_id": "departments_api", "url": "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/departments", ...}
```
> [!IMPORTANT]
> **Manual step needed from you:** Open DevTools Network tab on
> `https://daffodilvarsity.edu.bd/programs`, `faculties`, `clubs`, `contact`
> and tell me which API endpoints appear (XHR/Fetch). I'll add them to the sources.

---

### Phase 2 — Important (Medium Priority) — ~2 hours

#### [NEW] `app/cache.py`
Simple in-memory question cache with 1-hour TTL:
```python
def get_cached(question: str) -> Optional[str]: ...
def set_cache(question: str, answer: str) -> None: ...
```
Cache key = MD5 of lowercased, stripped question.

#### [MODIFY] `app/embedder.py` — Richer metadata
Add `category` and `last_updated` to each chunk:
```python
{
  "page_id":     "tuition_api",
  "url":         "...",
  "title":       "Tuition Fees (Domestic)",
  "category":    "fees",          # NEW
  "last_updated": "2026-07-03",   # NEW
  "chunk_text":  "...",
  "chunk_index": 0,
}
```

#### [MODIFY] `app/vector_store.py`
Store and retrieve `category` + `last_updated` from Qdrant payload.

#### [NEW] `data/` directory structure
Save raw API responses as JSON for debugging and auditing:
```
data/
  raw/
    fees_domestic.json
    fees_international.json
    admission.json
    programs.json
```

---

### Phase 3 — Optional / Future

#### Playwright integration
Only if a specific important page:
1. Has no API endpoint AND
2. BeautifulSoup returns empty/useless content

Install separately: `pip install playwright && playwright install chromium`

#### Related questions
Add a small second Groq call to generate 2-3 follow-up questions after each answer.
Not worth it on the free token tier — adds ~200 tokens per request.

---

## Verification Plan

### After Phase 1
- `curl -X POST /api/chat` 31 times → verify 30th/31st returns 429 Too Many Requests
- Kill Groq API key temporarily → verify retry + graceful error message
- Check `logs/requests.jsonl` has structured entries

### After Phase 2
- Ask same question twice → verify second response is instant (cached)
- Check `data/raw/fees_domestic.json` exists after refresh

---

## What You Need to Do Manually

> [!CAUTION]
> **Action required from you before I can add more APIs:**
>
> 1. Open Chrome → go to `https://daffodilvarsity.edu.bd/programs`
> 2. Press F12 → Network tab → filter by "Fetch/XHR"
> 3. Refresh the page
> 4. Screenshot or copy-paste any API calls to `webbackend.daffodilvarsity.edu.bd`
> 5. Do the same for `/faculties`, `/clubs`, `/contact-us`
>
> This lets me add them as API sources instead of relying on HTML scraping.

> [!NOTE]
> **`pip install` needed** — I'll add these to `requirements.txt`:
> - `slowapi` — rate limiting
> - `tenacity` — retry logic
> These are tiny (no binaries), safe to install in your existing venv.

---

## Summary — What Will Change

```
BEFORE                          AFTER
──────────────────────────────────────────────────────
No rate limiting            →   30 req/min per IP (SlowAPI)
No request logging          →   logs/requests.jsonl (structured)
No Groq retry               →   3 retries with 2s backoff (tenacity)
No answer cache             →   1-hour in-memory TTL cache
No category/last_updated    →   Rich chunk metadata
2 API sources               →   5+ API sources (+ programs, departments)
Crawler as primary          →   Crawler stays as fallback (correct role)
```
