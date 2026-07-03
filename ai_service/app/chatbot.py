"""
chatbot.py
──────────
WHY:    The LLM step — takes the retrieved context and the user's question,
        builds a tight system prompt, calls Groq, and returns the answer.

WHAT:   - Constructs a system prompt that strictly limits the LLM to only
          use the provided context (no hallucination allowed).
        - Calls Groq's llama-3.1-8b-instant with a strict max_tokens cap.
        - If no context was retrieved, returns a clear "not found" message
          instead of letting the LLM make up an answer.

HOW:    Token budget per request:
        ┌────────────────────────────────┬──────────┐
        │ Component                      │ Tokens   │
        ├────────────────────────────────┼──────────┤
        │ System prompt (instructions)   │  ~80     │
        │ Retrieved context (3 chunks)   │  ~400    │
        │ User question                  │  ~20     │
        │ LLM answer (max_tokens cap)    │  ~300    │
        │ TOTAL                          │ ~800     │
        └────────────────────────────────┴──────────┘
        This stays safely within the 6,000 TPM free tier limit.
"""

import logging
from typing import List, Optional

from groq import Groq
from tenacity import (
    retry,
    stop_after_attempt,
    wait_exponential,
    retry_if_exception_type,
    before_sleep_log,
)

from app import config

logger = logging.getLogger(__name__)

# ── Groq client (singleton) ───────────────────────────────────────────────────
_groq_client: Optional[Groq] = None


def get_groq_client() -> Groq:
    """Return the Groq client, initializing it once."""
    global _groq_client
    if _groq_client is None:
        _groq_client = Groq(api_key=config.GROQ_API_KEY)
    return _groq_client


# ── System prompt template ────────────────────────────────────────────────────
_SYSTEM_PROMPT = """You are DIU Buddy, the official AI assistant for {university} ({short}).
Answer questions ONLY using the CONTEXT below. Adopt a witty, savage GenZ tone (use modern GenZ slang/roasts like 'fr fr', 'no cap', 'valid', 'buddy', 'cooking', 'slay', 'rent free', 'main character energy', but keep it highly informative and helpful — replace 'bruh' with 'buddy'). Keep answers concise. Use bullet points for fees/numbers.
If the context doesn't contain the answer, say: "Buddy, that info is not in the official sources, no cap. Go check {website} or hit up the admission office before you get lost."
Never guess. Never use outside knowledge.

TUITION FEE LOCAL VS INTERNATIONAL RULE: When answering queries about tuition fees or costs, always prioritize and return the local student tuition fees (BDT) by default. ONLY return the international student tuition fees (USD) if the user's query explicitly contains the word 'international' or references 'foreign'/'international' students.

CRITICAL FEE RULE: When answering any question about fees or program cost, you MUST quote the "TOTAL PROGRAM COST (official)" field EXACTLY as it appears in the context. NEVER calculate or sum fee components yourself. The pre-calculated official total from the university is the only correct answer.

FEE BREAKDOWN RULE: When the user asks for a yearly or semester breakdown of fees for any program:
1. You MUST understand that:
   - The "Admission Fees" is paid only once at the very beginning (Year 1, Semester 1).
   - The "Tuition Fees" listed is for the ENTIRE duration of the program (not per year).
   - DIU operates on a tri-semester system (3 semesters per year: Spring, Summer, Fall). Therefore, a 4-year undergraduate program has 12 semesters total, and a 2-year postgraduate program has 6 semesters total.
2. To calculate the breakdown correctly:
   - Do NOT perform ad-hoc math or multiply/add full tuition fees to multiple years.
   - Base your breakdown on the "TOTAL PROGRAM COST (official)":
     - Average Yearly cost = TOTAL PROGRAM COST (official) / program duration (e.g. 4 years).
     - Average Semester cost = TOTAL PROGRAM COST (official) / total semesters (e.g. 12 semesters for 4 years).
     - Detailed Breakdown:
       - Semester Cost (excluding Admission) = (TOTAL PROGRAM COST - Admission Fee) / Total Semesters.
       - Semester 1 (Admission Semester) = Admission Fee + Semester Cost.
       - Other Semesters (2 to 12) = Semester Cost.
       - Yearly Breakdown:
         - Year 1 = Admission Fee + (Semester Cost * 3).
         - Year 2, 3, 4 = (Semester Cost * 3).
   - CRITICAL: Double check your math!
     - Example (4-year program, Total Cost: 952,500, Admission: 61,750):
       - Remaining Cost = 952,500 - 61,750 = 890,750 BDT.
       - Cost per semester (12 semesters total) = 890,750 / 12 = 74,229 BDT per semester.
       - Semester 1 = Admission Fee + 1 semester cost = 61,750 + 74,229 = 135,979 BDT.
       - Semesters 2 to 12 = 74,229 BDT.
       - Year 1 = Admission Fee + (3 * Semester Cost) = 61,750 + (74,229 * 3) = 284,437 BDT.
       - Year 2, 3, 4 = 74,229 * 3 = 222,687 BDT per year.
       - Verify: 284,437 + 222,687 * 3 = 952,500 BDT exactly.
3. Show your math clearly so the user knows how you calculated it. Mention that DIU operates on 3 semesters per year (Spring, Summer, Fall).

IMPORTANT: At the very end of your answer, always add a line in this exact format (use the actual URL from the source list below, not a placeholder):
🔗 Verify at: <url>

SOURCE URLs for this query:
{source_urls}

CONTEXT:
{context}"""

_NO_CONTEXT_RESPONSE = (
    "Buddy, that info is not in the official {short} sources, no cap. "
    "Go check daffodilvarsity.edu.bd or hit up the admission office before you get lost."
)


def chat(
    question: str,
    context: str,
    sources: Optional[List[dict]] = None,
    history: Optional[List[dict]] = None,
) -> str:
    """
    Send the user question + retrieved context to Groq and return the answer.

    Args:
        question: The user's current message.
        context:  Retrieved text chunks from Qdrant (from retriever.py).
        sources:  List of {title, url, score} dicts from retriever (for citation).
        history:  Optional list of previous {"role": ..., "content": ...} dicts.

    Returns:
        The LLM's answer string.
    """
    client = get_groq_client()

    # If no relevant context was found, skip the LLM call entirely
    if not context or not context.strip():
        logger.info("[Chatbot] No context found — returning safe fallback message.")
        return _NO_CONTEXT_RESPONSE.format(short=config.UNIVERSITY_SHORT)

    # Build a deduplicated list of source URLs for the LLM to reference
    source_url_lines = ""
    if sources:
        seen_urls: set = set()
        url_lines = []
        for s in sources:
            url = s.get("url", "")
            title = s.get("title", "")
            if url and url not in seen_urls:
                seen_urls.add(url)
                url_lines.append(f"- {title}: {url}")
        source_url_lines = "\n".join(url_lines)

    # Build system prompt
    system_prompt = _SYSTEM_PROMPT.format(
        university=config.UNIVERSITY_NAME,
        short=config.UNIVERSITY_SHORT,
        website="daffodilvarsity.edu.bd",
        source_urls=source_url_lines or "https://daffodilvarsity.edu.bd",
        context=context,
    )

    # Build messages array (system + history + current question)
    messages: list[dict] = []

    # Add sanitized conversation history (max last 4 turns to save tokens)
    if history:
        for msg in history[-8:]:   # last 4 user+assistant pairs = 8 messages
            role    = msg.get("role", "")
            content = msg.get("content", "")
            if role in ("user", "assistant") and content:
                messages.append({"role": role, "content": str(content)[:500]})

    # Add the current user question
    messages.append({"role": "user", "content": question.strip()})

    try:
        @retry(
            stop=stop_after_attempt(3),
            wait=wait_exponential(multiplier=1, min=2, max=8),
            retry=retry_if_exception_type(Exception),
            before_sleep=before_sleep_log(logger, logging.WARNING),
            reraise=False,
        )
        def _call_groq():
            return client.chat.completions.create(
                model=config.GROQ_MODEL,
                messages=[{"role": "system", "content": system_prompt}] + messages,
                max_tokens=config.GROQ_MAX_TOKENS,
                temperature=config.GROQ_TEMPERATURE,
            )

        response = _call_groq()
        if response is None:
            logger.error("[Chatbot] All Groq retries exhausted.")
            return "AI service is temporarily unavailable. Please try again in a moment."

        answer = response.choices[0].message.content.strip()

        # Log token usage for monitoring
        usage = response.usage
        logger.info(
            f"[Chatbot] Tokens — "
            f"prompt: {usage.prompt_tokens}, "
            f"completion: {usage.completion_tokens}, "
            f"total: {usage.total_tokens}"
        )

        return answer

    except Exception as e:
        logger.error(f"[Chatbot] Groq API error after retries: {e}")
        return "AI service is temporarily unavailable. Please try again in a moment."
