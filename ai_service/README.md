# DIU AI Assistant — Python RAG Microservice

A production-ready RAG (Retrieval-Augmented Generation) chatbot for **Daffodil International University**. 
Runs as a microservice inside `Campus_Buddy_v2/ai_service/` and is called by the Laravel app.

---

## Architecture

```
Browser (Campus Buddy Laravel app)
     ↓ POST /api/buddy-visitor
BuddyAIController.php (Laravel)
     ↓ HTTP POST /api/chat
FastAPI (ai_service/) ← YOU ARE HERE
     ↓
Qdrant Cloud (vector similarity search)
     ↓ top-3 matching chunks
Groq API (llama-3.1-8b-instant)
     ↓
Answer
```

---

## Setup (Local)

### Prerequisites
- Python 3.11+
- A free [Qdrant Cloud](https://cloud.qdrant.io) account
- Your Groq API key

### Step 1 — Create Qdrant Cloud Collection
1. Go to [cloud.qdrant.io](https://cloud.qdrant.io)
2. Create a free cluster
3. Copy your **Cluster URL** and create an **API Key**

### Step 2 — Configure Environment
```bash
cd Campus_Buddy_v2/ai_service/
cp .env.example .env
# Edit .env and fill in GROQ_API_KEY, QDRANT_URL, QDRANT_API_KEY
```

### Step 3 — Install Dependencies
```bash
cd Campus_Buddy_v2/ai_service/
python3 -m venv venv
source venv/bin/activate        # Windows: venv\Scripts\activate
pip install -r requirements.txt
```

### Step 4 — Run the Service
```bash
uvicorn app.main:app --reload --port 8000
```

On first start, it will:
1. Connect to Qdrant Cloud
2. Automatically scrape all official DIU pages
3. Chunk, embed, and store everything in Qdrant
4. Start the 24-hour background refresh scheduler

### Step 5 — Test It
- Open: http://localhost:8000 — see the chatbot UI
- Open: http://localhost:8000/docs — Swagger API docs
- Health check: http://localhost:8000/api/status

### Step 6 — Test with Laravel
In the Laravel `.env`, make sure:
```
VISITOR_AI_URL=http://127.0.0.1:8000
```
Then run both servers and try the Visitor AI on the Campus Buddy site.

---

## Deployment to Render

### Step 1 — Push to GitHub
```bash
git add ai_service/
git commit -m "feat: add Python RAG microservice"
git push
```

### Step 2 — Create Render Service
1. Go to [render.com](https://render.com) → New → Web Service
2. Connect your GitHub repo
3. Set **Root Directory** to `ai_service`
4. Set **Build Command**: `pip install -r requirements.txt`
5. Set **Start Command**: `uvicorn app.main:app --host 0.0.0.0 --port $PORT`

### Step 3 — Add Environment Variables in Render Dashboard
Copy all values from your `.env` file into the Render "Environment" tab.
**Never commit `.env` to git.**

### Step 4 — Update Laravel on Render
In your Campus Buddy Laravel app's Render environment:
```
VISITOR_AI_URL=https://your-diu-ai-service.onrender.com
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET`  | `/`      | Chatbot UI (HTML) |
| `POST` | `/api/chat` | Send a message, get an answer |
| `GET`  | `/api/status` | Health check + Qdrant stats |
| `POST` | `/api/refresh` | Manually trigger knowledge base refresh |
| `GET`  | `/docs`  | Swagger interactive API docs |

### POST /api/chat
```json
// Request
{ "message": "What is the tuition fee for CSE?", "history": [] }

// Response
{
  "answer": "The tuition fee for B.Sc. in CSE is ৳1,020,450 BDT total...",
  "sources": [{"title": "Tuition Fees", "url": "...", "score": 0.92}],
  "found": true
}
```

### POST /api/refresh
```json
// Request (requires secret from .env)
{ "secret": "your_refresh_secret", "force": false }
```

---

## How Auto-Update Works

Every 24 hours (configurable in `.env`):
1. Re-scrapes all official DIU pages
2. Computes MD5 hash of each page's text
3. Compares with stored hashes
4. Only re-embeds and re-indexes pages that have CHANGED
5. Skips unchanged pages (fast and efficient)

**Result**: Within 24 hours of DIU updating their website, the chatbot automatically knows.

---

## Token Budget

| Component | Tokens |
|-----------|--------|
| System prompt | ~80 |
| Retrieved context (top 3 chunks) | ~400 |
| User question | ~20 |
| LLM answer | ~250 |
| **Total per request** | **~750** |

Well within Groq's 6,000 TPM free limit.

---

## Common Errors

| Error | Solution |
|-------|----------|
| `Missing required environment variables` | Check your `.env` file |
| `Connection refused` to Qdrant | Check `QDRANT_URL` and `QDRANT_API_KEY` |
| Groq 413 error | Message or context too large — reduce `CHUNK_SIZE_TOKENS` in `config.py` |
| `No relevant chunks found` | Run `POST /api/refresh` to rebuild the knowledge base |
| Empty Qdrant collection | First startup — wait for the initial scrape to complete (~2-3 min) |

---

## Project Files

| File | Purpose |
|------|---------|
| `app/config.py` | All env vars and constants |
| `app/scraper.py` | BeautifulSoup4 scraper for all DIU pages |
| `app/embedder.py` | Text chunking + sentence-transformers embeddings |
| `app/vector_store.py` | Qdrant Cloud operations (upsert, search, delete) |
| `app/retriever.py` | Embed query → search Qdrant → return context |
| `app/chatbot.py` | Groq API integration + strict prompt |
| `app/scheduler.py` | APScheduler 24h auto-refresh pipeline |
| `app/main.py` | FastAPI app + all HTTP routes |
| `templates/index.html` | Chatbot HTML UI |
| `static/style.css` | Dark mode responsive CSS |
| `static/chat.js` | Vanilla JS chat logic |
| `render.yaml` | Render deployment config |
