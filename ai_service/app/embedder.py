"""
embedder.py
───────────
WHY:    Converts human-readable text into numbers (vectors) so that
        mathematical similarity search is possible. Without embeddings,
        we cannot search the vector database.

WHAT:   - Loads the sentence-transformers model once at startup (cached).
        - Splits long text into manageable chunks (≤ 400 tokens each).
        - Converts each chunk into a 384-dimensional vector.

HOW:    Uses the 'all-MiniLM-L6-v2' model, which is:
        - Small (~90MB download, runs on CPU)
        - Fast (embeds hundreds of sentences per second)
        - High quality (top-performing small model on benchmarks)
        - 100% free — runs entirely locally, no API calls.

        Text is split on sentence boundaries to preserve meaning.
        Overlapping tokens (50 tokens) between chunks ensure that a
        sentence spanning a chunk boundary is not lost.
"""

import logging
import re
from typing import List

import os
import requests
import time

from app import config

logger = logging.getLogger(__name__)

try:
    from sentence_transformers import SentenceTransformer
    HAS_SENTENCE_TRANSFORMERS = True
except ImportError:
    HAS_SENTENCE_TRANSFORMERS = False

logger.info(f"[Embedder] Local SentenceTransformer available: {HAS_SENTENCE_TRANSFORMERS}")

_model = None


def get_local_model():
    """Load the local SentenceTransformer model lazily to save startup memory."""
    global _model
    if _model is None and HAS_SENTENCE_TRANSFORMERS:
        logger.info(f"[Embedder] Loading local model: {config.EMBEDDING_MODEL}")
        _model = SentenceTransformer(config.EMBEDDING_MODEL)
        logger.info("[Embedder] Local model loaded successfully.")
    return _model


def embed_texts_via_api(texts: List[str]) -> List[List[float]]:
    """
    Query Hugging Face's serverless Inference API for all-MiniLM-L6-v2 embeddings.
    This runs entirely on HF cloud, requiring 0MB of local server memory.
    """
    hf_token = os.getenv("HF_TOKEN", "")
    api_url = f"https://api-inference.huggingface.co/models/sentence-transformers/{config.EMBEDDING_MODEL}"
    
    headers = {}
    if hf_token:
        headers["Authorization"] = f"Bearer {hf_token}"
        
    logger.info(f"[Embedder] Querying Hugging Face API for {len(texts)} embeddings...")
    
    # Hugging Face serverless API might need a retry if the model is loading
    for attempt in range(5):
        try:
            response = requests.post(
                api_url,
                headers=headers,
                json={"inputs": texts, "options": {"wait_for_model": True}},
                timeout=30
            )
            
            if response.status_code == 200:
                result = response.json()
                if isinstance(result, list):
                    # For some inputs, HF returns a nested list structure depending on the pipeline
                    # We want to make sure it's a 2D list of floats [num_texts, 384]
                    # If it's a single list of floats, wrap it
                    if len(texts) == 1 and isinstance(result[0], float):
                        return [result]
                    # If it returns a 3D list (due to token embeddings), pool it by taking the mean or first element
                    # But the default sentence-transformers model endpoint returns a 2D list of shape [num_texts, 384] directly.
                    return result
                raise ValueError(f"Unexpected response format from Hugging Face: {type(result)}")
                
            elif response.status_code == 503:
                # Model is loading
                data = response.json()
                wait_time = data.get("estimated_time", 5)
                logger.warning(f"[Embedder] HF model is loading, waiting {wait_time}s... (attempt {attempt+1}/5)")
                time.sleep(wait_time)
                continue
            else:
                raise ValueError(f"Hugging Face API returned status {response.status_code}: {response.text}")
                
        except Exception as e:
            if attempt == 4:
                raise e
            logger.warning(f"[Embedder] HF embedding attempt {attempt+1} failed: {e}. Retrying in 2s...")
            time.sleep(2)
            
    raise RuntimeError("Failed to get embeddings from Hugging Face API after 5 attempts.")


def embed_texts(texts: List[str]) -> List[List[float]]:
    """
    Convert a list of text strings into a list of embedding vectors.
    Each vector is a list of 384 floats.
    """
    if not texts:
        return []

    # 1. Use local SentenceTransformer if package is installed
    if HAS_SENTENCE_TRANSFORMERS:
        try:
            model = get_local_model()
            if model is not None:
                vectors = model.encode(texts, convert_to_numpy=True, show_progress_bar=False)
                return vectors.tolist()
        except Exception as e:
            logger.warning(f"[Embedder] Local model encoding failed, falling back to Hugging Face API: {e}")

    # 2. Otherwise query Hugging Face Serverless Inference API
    return embed_texts_via_api(texts)


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
