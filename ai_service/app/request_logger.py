"""
request_logger.py
─────────────────
WHY:    Without logs we are blind. This module records every chat request
        so we can monitor: common questions, slow queries, error rates,
        and whether the vector search is finding relevant content.

WHAT:   Writes one JSON line per request to logs/requests.jsonl.
        Each line contains:
            timestamp, question, response_time_ms, chunks_retrieved,
            top_score, found, cached, error

HOW:    Called at the end of every /api/chat request.
        append-only, one JSON object per line (JSONL format).
        File rotates when it exceeds 10 MB (keeps last 5 files).
"""

import json
import logging
import os
from datetime import datetime, timezone
from pathlib import Path
from typing import List, Optional

logger = logging.getLogger(__name__)

# ── Log file location ─────────────────────────────────────────────────────────
_LOG_DIR  = Path(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))) / "logs"
_LOG_FILE = _LOG_DIR / "requests.jsonl"
_MAX_BYTES = 10 * 1024 * 1024   # 10 MB per file


def _rotate_if_needed() -> None:
    """Rotate log file if it exceeds _MAX_BYTES."""
    if _LOG_FILE.exists() and _LOG_FILE.stat().st_size >= _MAX_BYTES:
        rotated = _LOG_FILE.with_suffix(f".{datetime.now().strftime('%Y%m%d_%H%M%S')}.jsonl")
        _LOG_FILE.rename(rotated)
        logger.info(f"[RequestLogger] Rotated log file → {rotated.name}")


def log_request(
    question:         str,
    response_time_ms: int,
    chunks_retrieved: int,
    top_score:        Optional[float],
    found:            bool,
    cached:           bool   = False,
    error:            Optional[str] = None,
) -> None:
    """
    Append one log record (JSON line) for a chat request.

    Args:
        question:         The user's question.
        response_time_ms: Total time from request received to response sent.
        chunks_retrieved: Number of Qdrant chunks returned.
        top_score:        Highest similarity score from Qdrant (0–1).
        found:            Whether any relevant context was found.
        cached:           Whether this answer was served from cache.
        error:            Error message if an exception occurred.
    """
    try:
        _LOG_DIR.mkdir(parents=True, exist_ok=True)
        _rotate_if_needed()

        record = {
            "timestamp":         datetime.now(timezone.utc).isoformat(),
            "question":          question[:200],   # cap length for safety
            "response_time_ms":  response_time_ms,
            "chunks_retrieved":  chunks_retrieved,
            "top_score":         round(top_score, 4) if top_score else None,
            "found":             found,
            "cached":            cached,
            "error":             error,
        }

        with open(_LOG_FILE, "a", encoding="utf-8") as f:
            f.write(json.dumps(record) + "\n")

    except Exception as e:
        # Never let logging errors crash the API
        logger.warning(f"[RequestLogger] Failed to write log: {e}")
