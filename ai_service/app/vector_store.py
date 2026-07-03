"""
vector_store.py
───────────────
WHY:    FAISS stores vectors on the local disk. On Render (free hosting),
        the disk is wiped on every restart/redeploy. We use Qdrant Cloud
        instead — a free, persistent cloud vector database that survives
        restarts and keeps our knowledge base intact indefinitely.

WHAT:   - Creates the Qdrant collection on first run (if it doesn't exist).
        - Provides three operations:
            1. upsert_page()  — add or replace all chunks for a page.
            2. search()       — find the top-K most similar chunks.
            3. delete_page()  — remove all chunks for a page (before re-indexing).

HOW:    Each chunk becomes one "point" in Qdrant with:
        - A unique UUID id
        - A 384-float vector (the embedding)
        - Payload metadata: page_id, url, title, chunk_text, chunk_index

        The page_id in the payload lets us efficiently delete all chunks
        from a specific page using Qdrant's filter-based deletion.
"""

import logging
import uuid
from typing import List, Optional

from qdrant_client import QdrantClient
from qdrant_client.models import (
    Distance,
    FieldCondition,
    Filter,
    MatchValue,
    PayloadSchemaType,
    PointStruct,
    VectorParams,
)

from app import config

logger = logging.getLogger(__name__)

# ── Qdrant client (singleton) ─────────────────────────────────────────────────
_client: Optional[QdrantClient] = None


def get_client() -> QdrantClient:  # type: ignore[return]
    """Return the Qdrant client, initializing it once."""
    global _client
    if _client is None:
        logger.info("[VectorStore] Connecting to Qdrant Cloud...")
        _client = QdrantClient(
            url=config.QDRANT_URL,
            api_key=config.QDRANT_API_KEY,
            timeout=30,
        )
        logger.info("[VectorStore] Connected.")
    return _client


def ensure_collection_exists() -> None:
    """
    Create the Qdrant collection if it doesn't already exist.
    Uses cosine similarity (best for sentence-transformer embeddings).
    Safe to call multiple times — no-ops if collection already exists.
    """
    client = get_client()
    existing = [c.name for c in client.get_collections().collections]

    if config.QDRANT_COLLECTION not in existing:
        logger.info(f"[VectorStore] Creating collection: {config.QDRANT_COLLECTION}")
        client.create_collection(
            collection_name=config.QDRANT_COLLECTION,
            vectors_config=VectorParams(
                size=config.EMBEDDING_DIM,
                distance=Distance.COSINE,
            ),
        )
        # Create a keyword index on page_id so filter-based deletes work
        client.create_payload_index(
            collection_name=config.QDRANT_COLLECTION,
            field_name="page_id",
            field_schema=PayloadSchemaType.KEYWORD,
        )
        logger.info("[VectorStore] Collection created successfully.")
    else:
        logger.info(f"[VectorStore] Collection '{config.QDRANT_COLLECTION}' already exists.")

    # Always ensure the page_id keyword index exists (no-op if already present)
    try:
        client.create_payload_index(
            collection_name=config.QDRANT_COLLECTION,
            field_name="page_id",
            field_schema=PayloadSchemaType.KEYWORD,
        )
        logger.info("[VectorStore] page_id payload index ensured.")
    except Exception:
        pass  # Index already exists — safe to ignore


def delete_page(page_id: str) -> None:
    """
    Delete all vector points belonging to a specific page.
    Called before re-indexing an updated page so old data doesn't mix
    with new data.

    Args:
        page_id: The unique identifier of the page (e.g., 'admission').
    """
    client = get_client()
    logger.info(f"[VectorStore] Deleting existing vectors for page: {page_id}")
    client.delete(
        collection_name=config.QDRANT_COLLECTION,
        points_selector=Filter(
            must=[FieldCondition(key="page_id", match=MatchValue(value=page_id))]
        ),
    )


def upsert_page(chunks: List[dict]) -> int:
    """
    Upload embedded chunks for a page to Qdrant.
    Generates a unique UUID for each chunk point.

    Args:
        chunks: List of chunk dicts (must include 'embedding' key).

    Returns:
        Number of points successfully upserted.
    """
    if not chunks:
        return 0

    client = get_client()
    points = []

    for chunk in chunks:
        point = PointStruct(
            id=str(uuid.uuid4()),   # unique ID for this vector point
            vector=chunk["embedding"],
            payload={
                "page_id":     chunk["page_id"],
                "url":         chunk["url"],
                "title":       chunk["title"],
                "chunk_text":  chunk["chunk_text"],
                "chunk_index": chunk["chunk_index"],
            },
        )
        points.append(point)

    # Upload in batches of 100 to avoid timeout on large pages
    batch_size = 100
    total_uploaded = 0
    for i in range(0, len(points), batch_size):
        batch = points[i : i + batch_size]
        client.upsert(
            collection_name=config.QDRANT_COLLECTION,
            points=batch,
            wait=True,
        )
        total_uploaded += len(batch)

    logger.info(
        f"[VectorStore] Upserted {total_uploaded} points "
        f"for page '{chunks[0]['page_id']}' / '{chunks[0]['title']}'"
    )
    return total_uploaded


def search(query_vector: List[float], top_k: int = 3) -> List[dict]:
    """
    Find the most semantically similar chunks to a query vector.

    Args:
        query_vector: 384-float embedding of the user's question.
        top_k:        Number of results to return.

    Returns:
        List of result dicts:
        {
            'score':      float,  # cosine similarity (0-1, higher = better)
            'page_id':    str,
            'url':        str,
            'title':      str,
            'chunk_text': str,
        }
    """
    client = get_client()

    results = client.search(
        collection_name=config.QDRANT_COLLECTION,
        query_vector=query_vector,
        limit=top_k,
        with_payload=True,
        score_threshold=0.30,   # ignore results with very low similarity
    )

    hits = []
    for r in results:
        hits.append({
            "score":      round(r.score, 4),
            "page_id":    r.payload.get("page_id", ""),
            "url":        r.payload.get("url", ""),
            "title":      r.payload.get("title", ""),
            "chunk_text": r.payload.get("chunk_text", ""),
        })

    return hits


def get_collection_stats() -> dict:
    """Return basic stats about the Qdrant collection (used by /api/status)."""
    client = get_client()
    info = client.get_collection(config.QDRANT_COLLECTION)
    return {
        "collection":    config.QDRANT_COLLECTION,
        "total_vectors": info.points_count,
        "status":        str(info.status),
    }
