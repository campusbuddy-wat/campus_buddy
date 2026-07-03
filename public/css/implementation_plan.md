# Dynamic Visitor AI with Real-Time Web Retrieval (RAG)

Make the Visitor AI truly dynamic by fetching live data from official DIU websites instead of relying on a static hardcoded knowledge base. The AI will retrieve up-to-date information on admissions, scholarships, programs, and campus news directly from source URLs before each response.

---

## How it Works (Architecture)

```
Visitor asks a question
        ↓
DIUWebScraperService fetches & caches relevant pages
        ↓
RAGService builds prompt with live web content injected
        ↓
Groq AI answers using real, current data
```

---

## Key Design Decisions

> [!IMPORTANT]
> **Scraping on every request is too slow.** Each page fetch can take 2–5 seconds. We will use **Laravel Cache** to store scraped content for **6 hours** per source URL. The AI still gets fresh, real data — just not live per-second.

> [!NOTE]
> **No new composer packages needed.** Laravel's built-in `Http` facade (which uses Guzzle under the hood) is already installed and sufficient for fetching pages. We will strip HTML using PHP's native `strip_tags()` to extract clean text.

> [!WARNING]
> **Fallback is critical.** If any DIU website is down or returns an error, we fall back to the existing static knowledge base so the Visitor AI never goes offline.

---

## Sources to Scrape

| Source | URL | What we get |
|--------|-----|-------------|
| DIU Main | `https://daffodilvarsity.edu.bd` | General info, vision, mission |
| Admissions | `https://admission.daffodilvarsity.edu.bd` | Live intake deadlines, process |
| Scholarship | `https://daffodilvarsity.edu.bd/scholarship/diu-scholarship` | Waiver criteria, amounts |
| Departments | `https://daffodilvarsity.edu.bd/departments` | Faculty & program list |
| Fee Structure | `https://daffodilvarsity.edu.bd/tuition-fees` | Latest credit-based fees |
| Facilities | `https://dsc.creative-bd.com/` | hostel |
| Transportation | `https://daffodilvarsity.edu.bd/transport` | transport |
| Labs | `https://daffodilvarsity.edu.bd/lab-facilities` | labs |
| News/Events | `https://news.daffodilvarsity.edu.bd/` | Recent announcements |

---

## Proposed Changes

### New Service: `DIUWebScraperService`

#### [NEW] [DIUWebScraperService.php](file:///Users/washimakram/Campus_Buddy_v2/app/Services/DIUWebScraperService.php)

A dedicated service that:
1. Defines a list of DIU source URLs with labels
2. Fetches each page using `Http::get()` with a 10s timeout
3. Strips HTML tags and collapses whitespace for clean text
4. Caches each page's text for **6 hours** using Laravel Cache
5. Has a `fetchAllSources()` method that returns a structured array: `['label' => 'text content']`
6. Falls back gracefully (returns empty string) if a page is unreachable

---

### Modified Service: `RAGService`

#### [MODIFY] [RAGService.php](file:///Users/washimakram/Campus_Buddy_v2/app/Services/RAGService.php)

- Replace the hardcoded `buildVisitorSystemPrompt()` with a dynamic version
- It will inject `DIUWebScraperService` and call `fetchAllSources()`
- The live scraped content is inserted into the system prompt under a `## LIVE WEB DATA` section
- Static facts are kept as a **fallback layer** — only used if web data is empty or an error occurs
- The prompt now tells the AI: *"Prefer the live web data over any prior knowledge. Quote sources where relevant."*

---

### Modified Controller: `BuddyAIController`

#### [MODIFY] [BuddyAIController.php](file:///Users/washimakram/Campus_Buddy_v2/app/Http/Controllers/BuddyAIController.php)

- The `visitorChat()` call to `buildVisitorSystemPrompt()` stays the same — no changes needed here since the dynamic logic is fully inside `RAGService`.
- *(No controller changes required)*

---

### Optional: Artisan Command to Pre-warm Cache

#### [NEW] [WarmVisitorAICache.php](file:///Users/washimakram/Campus_Buddy_v2/app/Console/Commands/WarmVisitorAICache.php)

An Artisan command `php artisan visitor-ai:warm-cache` that pre-fetches all DIU sources and stores them in cache. This can be scheduled in the Kernel to run every 6 hours so the first visitor never waits for a cold fetch.

---

## Verification Plan

### Automated
- `php artisan visitor-ai:warm-cache` — verify all sources are fetched and cached without errors

### Manual Verification
1. Clear cache: `php artisan cache:clear`
2. Send a visitor chat message asking: *"What are the current admission deadlines?"*
3. Confirm the AI mentions live data from the website, not just the old static text
4. Confirm response time is acceptable (first request may be ~3-5s, subsequent fast from cache)
5. Test fallback: temporarily set a wrong URL and confirm the AI still responds with static facts
