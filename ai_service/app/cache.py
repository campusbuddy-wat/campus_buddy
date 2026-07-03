"""
cache.py
────────
WHY:    If the same student asks "What is CSE fee?" twice, there is no reason
        to call Groq again. Caching saves tokens, reduces latency, and avoids
        hitting the free-tier rate limit.

WHAT:   In-memory TTL cache keyed by MD5(lowercased question).
        Default TTL: 1 hour. Max entries: 500 (LRU eviction).

HOW:    Simple dict + timestamp. No Redis needed — a university chatbot
        does not have millions of users, so in-process memory is fine.
        Cache is cleared on server restart (intentional — keeps data fresh).
"""

import hashlib
import logging
import time
from typing import Optional

logger = logging.getLogger(__name__)

# ── Configuration ─────────────────────────────────────────────────────────────
_TTL_SECONDS: int = 3600    # 1 hour
_MAX_ENTRIES: int = 500

# ── Internal store: {cache_key: (answer, sources, timestamp)} ─────────────────
_store: dict = {}


def _key(question: str) -> str:
    """Normalize and hash the question for use as a cache key."""
    normalized = question.strip().lower()
    return hashlib.md5(normalized.encode("utf-8")).hexdigest()


def get(question: str) -> Optional[dict]:
    """
    Return the cached result for this question, or None if not found / expired.

    Returns dict with {answer, sources, found} matching ChatResponse shape.
    """
    k = _key(question)
    entry = _store.get(k)
    if not entry:
        return None
    answer, sources, ts = entry
    if time.time() - ts > _TTL_SECONDS:
        del _store[k]
        logger.debug(f"[Cache] Expired entry for: '{question[:60]}'")
        return None
    logger.info(f"[Cache] HIT for: '{question[:60]}'")
    return {"answer": answer, "sources": sources, "found": True, "cached": True}


def set(question: str, answer: str, sources: list) -> None:
    """Store the answer for this question in the cache."""
    # Evict oldest if at capacity
    if len(_store) >= _MAX_ENTRIES:
        oldest_key = min(_store, key=lambda k: _store[k][2])
        del _store[oldest_key]
        logger.debug("[Cache] Evicted oldest entry (LRU)")

    k = _key(question)
    _store[k] = (answer, sources, time.time())
    logger.info(f"[Cache] SET for: '{question[:60]}'")


def clear() -> int:
    """Clear all cache entries. Returns number of entries cleared."""
    count = len(_store)
    _store.clear()
    logger.info(f"[Cache] Cleared {count} entries")
    return count


def stats() -> dict:
    """Return cache statistics."""
    now = time.time()
    active = sum(1 for v in _store.values() if now - v[2] <= _TTL_SECONDS)
    return {
        "total_entries":  len(_store),
        "active_entries": active,
        "expired_entries": len(_store) - active,
        "ttl_seconds":    _TTL_SECONDS,
        "max_entries":    _MAX_ENTRIES,
    }
