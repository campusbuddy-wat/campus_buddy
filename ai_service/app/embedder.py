"""
embedder.py
───────────
WHY:    Converts human-readable text into numbers (vectors) so that
        mathematical similarity search is possible. Without embeddings,
        we cannot search the vector database.

WHAT:   - Loads the fastembed ONNX model once at startup (cached).
        - Splits long text into manageable chunks (≤ 400 tokens each).
        - Converts each chunk into a 384-dimensional vector.

HOW:    Uses fastembed with 'all-MiniLM-L6-v2' model, which is:
        - Very small ONNX runtime (~100MB total, NO PyTorch needed)
        - Fast (embeds hundreds of sentences per second on CPU)
        - High quality (same model used by sentence-transformers)
        - 100% free — runs entirely locally, zero network calls at runtime.
        - Fits comfortably within Render's 512MB free-tier RAM limit.

        Text is split on sentence boundaries to preserve meaning.
        Overlapping tokens (50 tokens) between chunks ensure that a
        sentence spanning a chunk boundary is not lost.
"""

import logging
import re
from typing import List

from app import config

logger = logging.getLogger(__name__)

# ── Model Loading (lazy singleton) ───────────────────────────────────────────

_model = None


def get_model():
    """
    Lazily load the fastembed TextEmbedding model on first use.
    fastembed uses ONNX Runtime — no PyTorch, ~100MB RAM total.
    """
    global _model
    if _model is None:
        try:
            from fastembed import TextEmbedding
            logger.info(f"[Embedder] Loading fastembed model: {config.EMBEDDING_MODEL}")
            _model = TextEmbedding(model_name=config.EMBEDDING_MODEL)
            logger.info("[Embedder] fastembed model loaded successfully.")
        except Exception as e:
            logger.error(f"[Embedder] Failed to load fastembed model: {e}")
            raise
    return _model


# ── Public Embedding API ─────────────────────────────────────────────────────

def embed_texts(texts: List[str]) -> List[List[float]]:
    """
    Convert a list of text strings into a list of embedding vectors.
    Each vector is a list of 384 floats.
    Uses fastembed (ONNX) — runs locally with ~100MB RAM, no network calls.
    """
    if not texts:
        return []

    model = get_model()
    # fastembed returns a generator of numpy arrays
    vectors = list(model.embed(texts))
    return [v.tolist() for v in vectors]


def embed_query(query: str) -> List[float]:
    """
    Embed a single user query into a vector for similarity search.
    """
    vectors = embed_texts([query])
    if not vectors:
        raise ValueError("Failed to compute embedding for query")
    return vectors[0]


# ── Text Chunking ─────────────────────────────────────────────────────────────

def _approximate_token_count(text: str) -> int:
    """
    Fast approximation: average English word is ~1.3 tokens.
    Used for chunk sizing — no need for the exact tiktoken count.
    """
    words = len(text.split())
    return int(words * 1.3)


def _split_into_sentences(text: str) -> List[str]:
    """
    Split text on sentence-ending punctuation followed by whitespace.
    Handles abbreviations like 'Dr.' 'B.Sc.' reasonably well.
    """
    # Split on ". ", "! ", "? " followed by a capital letter or newline
    parts = re.split(r'(?<=[.!?])\s+(?=[A-Z\n])', text)
    # Also split on double newlines (paragraph breaks)
    sentences = []
    for part in parts:
        sub = re.split(r'\n{2,}', part)
        sentences.extend([s.strip() for s in sub if s.strip()])
    return sentences


def chunk_text(text: str, page_id: str, url: str, title: str) -> List[dict]:
    """
    Split a page's full text into overlapping chunks of ~400 tokens each.
    Each chunk becomes one vector in Qdrant.

    Args:
        text:    Full plain text of the page.
        page_id: Unique identifier for the source page.
        url:     Source URL (stored as metadata for attribution).
        title:   Human-readable page title.

    Returns:
        List of chunk dicts:
        {
            'page_id':    str,
            'url':        str,
            'title':      str,
            'chunk_text': str,
            'chunk_index':int,
        }
    """
    sentences = _split_into_sentences(text)
    chunks     = []
    chunk_idx  = 0

    current_sentences: List[str] = []
    current_tokens:    int       = 0

    for sentence in sentences:
        sentence_tokens = _approximate_token_count(sentence)

        # If adding this sentence would overflow the chunk, flush
        if current_tokens + sentence_tokens > config.CHUNK_SIZE_TOKENS and current_sentences:
            chunk_text_str = " ".join(current_sentences)
            chunks.append({
                "page_id":     page_id,
                "url":         url,
                "title":       title,
                "chunk_text":  chunk_text_str,
                "chunk_index": chunk_idx,
            })
            chunk_idx += 1

            # Overlap: keep last N tokens worth of sentences
            overlap_sentences: List[str] = []
            overlap_tokens = 0
            for s in reversed(current_sentences):
                t = _approximate_token_count(s)
                if overlap_tokens + t <= config.CHUNK_OVERLAP_TOKENS:
                    overlap_sentences.insert(0, s)
                    overlap_tokens += t
                else:
                    break
            current_sentences = overlap_sentences
            current_tokens    = overlap_tokens

        current_sentences.append(sentence)
        current_tokens += sentence_tokens

    # Flush remaining sentences
    if current_sentences:
        chunks.append({
            "page_id":     page_id,
            "url":         url,
            "title":       title,
            "chunk_text":  " ".join(current_sentences),
            "chunk_index": chunk_idx,
        })

    logger.info(
        f"[Embedder] '{title}' → {len(sentences)} sentences → {len(chunks)} chunks"
    )
    return chunks


def embed_chunks(chunks: List[dict]) -> List[dict]:
    """
    Add an 'embedding' key to each chunk dict.
    Returns the same list with embeddings added in-place.
    """
    texts = [c["chunk_text"] for c in chunks]
    vectors = embed_texts(texts)
    for chunk, vector in zip(chunks, vectors):
        chunk["embedding"] = vector
    return chunks
