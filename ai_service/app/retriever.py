"""
retriever.py
────────────
WHY:    The "brain connector" — bridges user questions to the knowledge base.
        Instead of sending all 50+ DIU web pages to the LLM (which would
        exceed token limits), this module finds ONLY the 2-3 paragraphs
        that are most relevant to the user's specific question.

WHAT:   Takes a user question string, converts it to a vector embedding,
        searches Qdrant for the closest matching chunks, and returns
        the retrieved text for the LLM to use.

HOW:    1. embed_query(question) → 384-float vector
        2. vector_store.search(vector, top_k=3) → top 3 matching chunks
        3. Return formatted context string + source list

RESULT: The LLM receives ~400 tokens of targeted context instead of
        12,000 tokens of raw website text. This keeps us well within
        the 6,000 TPM limit on the free Groq tier.
"""

import logging
from typing import Dict, List

from app import config
from app.embedder import embed_query
from app import vector_store

logger = logging.getLogger(__name__)


def retrieve(question: str) -> Dict:
    """
    Find the most relevant knowledge base chunks for a user question.

    Args:
        question: The user's raw question string.

    Returns:
        {
            'context':     str,          # formatted text to inject into LLM prompt
            'sources':     list[dict],   # list of {title, url, score} for attribution
            'found':       bool,         # False if no relevant chunks found
        }
    """
    if not question or not question.strip():
        return {"context": "", "sources": [], "found": False}

    import re
    expanded_question = question
    acronyms = {
        r"\bcse\b": "Computer Science and Engineering (CSE)",
        r"\bswe\b": "Software Engineering (SWE)",
        r"\bcs\b":  "Computer Science (CS)",
        r"\bbba\b": "Bachelor of Business Administration (BBA)",
        r"\bbe\b":  "Bachelor of Entrepreneurship (BE)",
        r"\bcis\b": "Computing and Information System (CIS)",
        r"\beee\b": "Electrical and Electronic Engineering (EEE)",
        r"\bjmc\b": "Journalism and Mass Communication (JMC)",
        r"\bete\b": "Electronics and Telecommunication Engineering (ETE)",
        r"\bmct\b": "Multimedia and Creative Technology (MCT)",
        r"\bnfe\b": "Nutrition and Food Engineering (NFE)",
        r"\bds\b":  "Data Science (DS)",
        r"\bce\b":  "Civil Engineering (CE)",
        r"\bte\b":  "Textile Engineering (TE)",
        r"\bre\b":  "Real Estate (RE)",
        r"\bthm\b": "Tourism and Hospitality Management (THM)",
        r"\besdm\b": "Environmental Science and Disaster Management (ESDM)",
        r"\bge\b":  "Genetic Engineering (GE)",
    }
    for pattern, full_name in acronyms.items():
        expanded_question = re.sub(pattern, full_name, expanded_question, flags=re.IGNORECASE)

    # Step 1: Embed the expanded user question
    query_vector = embed_query(expanded_question.strip())

    # Step 2: Search Qdrant for similar chunks
    hits = vector_store.search(query_vector, top_k=config.TOP_K_RESULTS)

    if not hits:
        logger.info(f"[Retriever] No relevant chunks found for: '{question[:80]}'")
        return {"context": "", "sources": [], "found": False}

    # Step 3: Build context block for the LLM
    context_parts = []
    sources       = []
    seen_chunks   = set()

    for i, hit in enumerate(hits, start=1):
        chunk_text = hit["chunk_text"].strip()

        # Deduplicate — sometimes overlapping chunks return the same text
        fingerprint = chunk_text[:100]
        if fingerprint in seen_chunks:
            continue
        seen_chunks.add(fingerprint)

        context_parts.append(
            f"[Source {i}: {hit['title']}]\n{chunk_text}"
        )
        sources.append({
            "title": hit["title"],
            "url":   hit["url"],
            "score": hit["score"],
        })

    if not context_parts:
        return {"context": "", "sources": [], "found": False}

    context = "\n\n".join(context_parts)

    logger.info(
        f"[Retriever] Retrieved {len(context_parts)} chunks "
        f"({len(context)} chars) for question: '{question[:60]}'"
    )

    return {
        "context": context,
        "sources": sources,
        "found":   True,
    }
