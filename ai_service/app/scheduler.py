"""
scheduler.py
────────────
WHY:    The DIU website changes — new admission deadlines are posted,
        tuition fees are updated, new clubs are added. Without an
        auto-refresh mechanism, the chatbot would give stale information.

WHAT:   Defines the `refresh_knowledge_base()` pipeline function that:
        1. Scrapes all official DIU pages.
        2. Checks if each page has changed (MD5 hash comparison).
        3. Re-embeds and re-upserts ONLY changed pages.
        4. Skips unchanged pages (saves time and API calls).

        Sets up APScheduler to call this function every 24 hours
        (configurable via REFRESH_INTERVAL_HOURS in .env).

HOW:    APScheduler runs as a background thread inside the same FastAPI
        process. No separate worker process or cron job is needed.
        The scheduler starts when FastAPI starts (via the lifespan context
        in main.py) and stops cleanly when the app shuts down.

        A hash cache (in-memory dict) tracks the MD5 of each page's
        last-scraped content so we can skip unchanged pages efficiently.
"""

import json
import logging
import os
from datetime import datetime
from pathlib import Path
from typing import Dict, Optional

from apscheduler.schedulers.background import BackgroundScheduler

from app import config
from app.scraper import scrape_all
from app.embedder import chunk_text, embed_chunks
from app import vector_store

logger = logging.getLogger(__name__)

# ── In-memory hash cache: {page_id: md5_hash} ────────────────────────────────
# Persisted to disk as data/processed/hash_cache.json so it survives restarts.
_HASH_CACHE_PATH = Path("data/processed/hash_cache.json")
_hash_cache: Dict[str, str] = {}

# ── APScheduler instance ──────────────────────────────────────────────────────
_scheduler: Optional[BackgroundScheduler] = None


def _load_hash_cache() -> None:
    """Load the hash cache from disk (called at startup)."""
    global _hash_cache
    if _HASH_CACHE_PATH.exists():
        try:
            _hash_cache = json.loads(_HASH_CACHE_PATH.read_text())
            logger.info(f"[Scheduler] Loaded hash cache: {len(_hash_cache)} entries")
        except Exception as e:
            logger.warning(f"[Scheduler] Could not load hash cache: {e}")
            _hash_cache = {}
    else:
        _hash_cache = {}


def _save_hash_cache() -> None:
    """Persist the hash cache to disk."""
    _HASH_CACHE_PATH.parent.mkdir(parents=True, exist_ok=True)
    _HASH_CACHE_PATH.write_text(json.dumps(_hash_cache, indent=2))


def refresh_knowledge_base(force: bool = False) -> dict:
    """
    Full pipeline: scrape → compare hashes → embed → upsert to Qdrant.
    Runs automatically every 24 hours and can be triggered manually.

    Args:
        force: If True, re-index all pages regardless of whether they changed.

    Returns:
        Summary dict with counts of updated/skipped pages.
    """
    start_time = datetime.now()
    logger.info(f"[Scheduler] Knowledge base refresh started at {start_time}")

    try:
        vector_store.ensure_collection_exists()
    except Exception as e:
        logger.error(f"[Scheduler] Could not connect to Qdrant: {e}")
        return {"status": "error", "message": str(e)}

    # Step 1: Scrape all official DIU sources
    scraped = scrape_all(delay_seconds=1.0)

    updated_pages = []
    skipped_pages = []
    failed_pages  = []

    for page in scraped:
        page_id   = page["page_id"]
        new_hash  = page["hash"]
        old_hash  = _hash_cache.get(page_id)

        # Step 2: Skip if content hasn't changed
        if not force and old_hash == new_hash:
            logger.info(f"[Scheduler] ↷ Skipping unchanged page: {page['title']}")
            skipped_pages.append(page_id)
            continue

        logger.info(f"[Scheduler] ↻ Updating: {page['title']}")

        try:
            # Step 3: Chunk the page text
            chunks = chunk_text(
                text=page["text"],
                page_id=page["page_id"],
                url=page["url"],
                title=page["title"],
            )

            if not chunks:
                logger.warning(f"[Scheduler] No chunks for {page['title']}, skipping.")
                failed_pages.append(page_id)
                continue

            # Step 4: Embed chunks
            chunks = embed_chunks(chunks)

            # Step 5: Delete old vectors for this page
            vector_store.delete_page(page_id)

            # Step 6: Upload new vectors
            count = vector_store.upsert_page(chunks)

            # Step 7: Update hash cache
            _hash_cache[page_id] = new_hash

            updated_pages.append({"page_id": page_id, "title": page["title"], "chunks": count})
            logger.info(f"[Scheduler] ✅ Updated '{page['title']}' → {count} chunks.")

        except Exception as e:
            logger.error(f"[Scheduler] ❌ Failed to update '{page['title']}': {e}")
            failed_pages.append(page_id)

    # Save updated hash cache
    _save_hash_cache()

    elapsed = (datetime.now() - start_time).total_seconds()
    summary = {
        "status":        "ok",
        "duration_sec":  round(elapsed, 1),
        "updated":       len(updated_pages),
        "skipped":       len(skipped_pages),
        "failed":        len(failed_pages),
        "updated_pages": updated_pages,
    }

    logger.info(
        f"[Scheduler] Refresh complete in {elapsed:.1f}s — "
        f"updated: {len(updated_pages)}, "
        f"skipped: {len(skipped_pages)}, "
        f"failed: {len(failed_pages)}"
    )

    return summary


def start_scheduler() -> None:
    """
    Start the APScheduler background scheduler.
    Called once when FastAPI starts (from the lifespan function in main.py).
    """
    global _scheduler

    _load_hash_cache()

    _scheduler = BackgroundScheduler(timezone="Asia/Dhaka")
    _scheduler.add_job(
        refresh_knowledge_base,
        trigger="interval",
        hours=config.REFRESH_INTERVAL_HOURS,
        id="knowledge_refresh",
        replace_existing=True,
        misfire_grace_time=600,   # allow 10 min late start if server was busy
    )
    _scheduler.start()

    total_vectors = 0
    try:
        stats = vector_store.get_collection_stats()
        total_vectors = stats.get("total_vectors", 0)
    except Exception:
        pass

    if total_vectors == 0:
        # First run — no data in Qdrant yet. Trigger an immediate scrape in a background thread
        # to avoid blocking FastAPI's startup / lifespan context (which causes 502 Bad Gateway timeouts on Render).
        import threading
        logger.info("[Scheduler] Qdrant collection is empty. Triggering initial scrape in background thread...")
        threading.Thread(target=refresh_knowledge_base, kwargs={"force": True}, daemon=True).start()
    else:
        logger.info(
            f"[Scheduler] Qdrant has {total_vectors} vectors. "
            f"Next refresh in {config.REFRESH_INTERVAL_HOURS}h."
        )

    logger.info(f"[Scheduler] Background scheduler started (every {config.REFRESH_INTERVAL_HOURS}h).")


def stop_scheduler() -> None:
    """Stop the scheduler cleanly on app shutdown."""
    global _scheduler
    if _scheduler and _scheduler.running:
        _scheduler.shutdown(wait=False)
        logger.info("[Scheduler] Scheduler stopped.")
