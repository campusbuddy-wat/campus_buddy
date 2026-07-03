"""
crawler.py
──────────
WHY:    A hardcoded URL list misses hundreds of DIU pages. A crawler
        starts from the homepage and automatically discovers every
        linked page within the official domain — so the knowledge
        base grows with the website, not just what we manually add.

WHAT:   BFS (breadth-first search) web crawler that:
        1. Starts at CRAWL_START_URL (https://daffodilvarsity.edu.bd/)
        2. Extracts all <a href> links from each page
        3. Filters: same domain only, HTML pages only, no auth pages
        4. Visits up to MAX_CRAWL_PAGES pages at max MAX_CRAWL_DEPTH levels
        5. Returns [{page_id, url, title}] dicts ready for scraper.py

HOW:    Uses collections.deque + visited set for BFS.
        Each page fetched with browser-like headers.
        Polite 1.5s delay between requests.

NEVER:  Follows external links, auth pages, or binary file downloads.
"""

import hashlib
import logging
import re
import time
from collections import deque
from typing import Dict, List, Set
from urllib.parse import urljoin, urldefrag, urlparse

import requests
from bs4 import BeautifulSoup

from app import config

logger = logging.getLogger(__name__)

# ── Allowed base domain (including all subdomains) ────────────────────────────
_ALLOWED_DOMAIN = "daffodilvarsity.edu.bd"

# ── File extensions that are NOT HTML pages — skip them ───────────────────────
_SKIP_EXTENSIONS: Set[str] = {
    ".pdf", ".jpg", ".jpeg", ".png", ".gif", ".svg", ".webp", ".bmp",
    ".zip", ".rar", ".tar", ".gz",
    ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx",
    ".mp4", ".mp3", ".avi", ".mov", ".wmv", ".wma", ".ogg",
    ".css", ".js", ".map", ".ico", ".xml", ".json", ".atom", ".rss",
    ".txt", ".csv",
}

# ── URL path patterns to skip (regex matched against full URL) ────────────────
_SKIP_PATTERNS: List[str] = [
    r"/login", r"/logout", r"/register", r"/signup",
    r"/admin", r"/dashboard", r"/profile", r"/account",
    r"/search\?", r"/tag/", r"/category/",
    r"/cdn-cgi/", r"/wp-admin/", r"/wp-json/",
    r"/print/", r"/rss", r"/feed",
    r"javascript:", r"mailto:", r"tel:",
]

# ── Browser-like headers ──────────────────────────────────────────────────────
_HEADERS: Dict[str, str] = {
    "User-Agent": (
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/120.0.0.0 Safari/537.36"
    ),
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "Accept-Language": "en-US,en;q=0.9",
}


def _is_crawlable(url: str) -> bool:
    """Return True if the URL is within the DIU domain and is an HTML page."""
    try:
        parsed = urlparse(url)
        if parsed.scheme not in ("http", "https"):
            return False
        host = parsed.netloc.lower().split(":")[0]
        if host != _ALLOWED_DOMAIN and not host.endswith("." + _ALLOWED_DOMAIN):
            return False
        path_lower = parsed.path.lower()
        if any(path_lower.endswith(ext) for ext in _SKIP_EXTENSIONS):
            return False
        url_lower = url.lower()
        if any(re.search(pat, url_lower) for pat in _SKIP_PATTERNS):
            return False
        return True
    except Exception:
        return False


def _normalize(url: str) -> str:
    """Normalize URL: strip fragment, lowercase scheme/host, strip trailing slash."""
    url, _ = urldefrag(url)
    parsed = urlparse(url)
    normalized = parsed._replace(
        scheme=parsed.scheme.lower(),
        netloc=parsed.netloc.lower(),
    ).geturl()
    if parsed.path not in ("", "/"):
        normalized = normalized.rstrip("/")
    return normalized


def _url_to_page_id(url: str) -> str:
    """
    Convert a URL to a clean page_id that is unique per URL.

    Examples:
        https://daffodilvarsity.edu.bd/                          → home
        https://daffodilvarsity.edu.bd/admission                 → admission
        https://daffodilvarsity.edu.bd/faculty/fsit              → faculty_fsit
        https://daffodilvarsity.edu.bd/programs?isUndergraduate  → programs_isUndergraduate_true
        https://research.daffodilvarsity.edu.bd/about            → research_about
    """
    try:
        parsed = urlparse(url)
        host   = parsed.netloc.lower().split(":")[0]  # strip port

        # Subdomain prefix (e.g. "research" from research.daffodilvarsity.edu.bd)
        subdomain = ""
        if host != _ALLOWED_DOMAIN and host.endswith("." + _ALLOWED_DOMAIN):
            sub = host[: -(len(_ALLOWED_DOMAIN) + 1)]
            subdomain = re.sub(r"[^a-zA-Z0-9]", "_", sub) + "_"

        # Path part
        path = parsed.path.strip("/")
        path_slug = re.sub(r"[^a-zA-Z0-9]", "_", path) if path else "home"
        path_slug = re.sub(r"_+", "_", path_slug).strip("_") or "home"

        # Query part (short hash to keep it readable)
        query_slug = ""
        if parsed.query:
            q_clean = re.sub(r"[^a-zA-Z0-9]", "_", parsed.query)
            q_clean = re.sub(r"_+", "_", q_clean).strip("_")
            query_slug = "_" + q_clean[:40]

        slug = subdomain + path_slug + query_slug

        # Hard limit
        if len(slug) > 90:
            suffix = hashlib.md5(url.encode()).hexdigest()[:8]
            slug = slug[:80] + "_" + suffix

        return slug or hashlib.md5(url.encode()).hexdigest()[:16]
    except Exception:
        return hashlib.md5(url.encode()).hexdigest()[:16]


# Generic SPA title that React apps put on every route — not useful
_GENERIC_TITLE_PATTERNS = [
    r"^daffodil international university",
    r"^diu\s*[|-]",
]


def _extract_title(soup: BeautifulSoup, url: str) -> str:
    """
    Extract a clean page title. Priority: <title> (if specific) → <h1> → URL path.
    Falls back to URL path for SPA pages where <title> is always the site name.
    """
    tag = soup.find("title")
    if tag:
        title = tag.get_text(strip=True)
        # Strip the site suffix
        title = re.sub(
            r"\s*[|\u2013\-]\s*(Daffodil|DIU).*$", "", title, flags=re.IGNORECASE
        ).strip()
        # If it's still a generic site-wide title, don't use it
        is_generic = any(
            re.match(pat, title, re.IGNORECASE) for pat in _GENERIC_TITLE_PATTERNS
        )
        if title and not is_generic:
            return title[:120]

    # Try h1
    h1 = soup.find("h1")
    if h1:
        h1_text = h1.get_text(strip=True)
        if h1_text:
            return h1_text[:120]

    # Fallback: build a readable title from the URL path
    parsed = urlparse(url)
    host   = parsed.netloc.lower()
    path   = parsed.path.strip("/")

    # Get meaningful last two segments
    parts = [p for p in path.split("/") if p]
    if parts:
        readable = " › ".join(
            seg.replace("-", " ").replace("_", " ").title() for seg in parts[-2:]
        )
    elif host != _ALLOWED_DOMAIN:
        # subdomain root: use subdomain name
        sub = host.replace("." + _ALLOWED_DOMAIN, "")
        readable = sub.replace("-", " ").replace("_", " ").title() + " (DIU)"
    else:
        readable = "DIU Home Page"

    # Append query context if any
    if parsed.query:
        q = parsed.query.replace("=", " ").replace("&", ", ").replace("+", " ")
        readable += f" ({q[:40]})"

    return readable[:120]


def _extract_links(soup: BeautifulSoup, base_url: str) -> List[str]:
    """Extract all absolute, crawlable, normalized links from the page."""
    seen: Set[str] = set()
    links: List[str] = []
    for tag in soup.find_all("a", href=True):
        raw = tag["href"].strip()
        if not raw:
            continue
        absolute  = urljoin(base_url, raw)
        normalized = _normalize(absolute)
        if normalized not in seen and _is_crawlable(normalized):
            seen.add(normalized)
            links.append(normalized)
    return links


def crawl(
    start_url: str = None,
    max_pages: int = None,
    max_depth: int = None,
    delay_seconds: float = 1.5,
) -> List[Dict]:
    """
    BFS crawl of the DIU website starting from start_url.

    Args:
        start_url:     Entry point URL (default: config.CRAWL_START_URL).
        max_pages:     Max HTML pages to collect (default: config.MAX_CRAWL_PAGES).
        max_depth:     Max link depth from start (default: config.MAX_CRAWL_DEPTH).
        delay_seconds: Polite pause between requests.

    Returns:
        List of {page_id, url, title} dicts (same format as SOURCES in scraper.py).
    """
    start_url = _normalize(start_url or config.CRAWL_START_URL)
    max_pages = max_pages or config.MAX_CRAWL_PAGES
    max_depth = max_depth or config.MAX_CRAWL_DEPTH

    logger.info("=" * 60)
    logger.info(f"[Crawler] BFS crawl start: {start_url}")
    logger.info(f"[Crawler] max_pages={max_pages}  max_depth={max_depth}")
    logger.info("=" * 60)

    visited: Set[str]   = {start_url}
    queue:   deque      = deque([(start_url, 0)])
    results: List[Dict] = []

    while queue and len(results) < max_pages:
        url, depth = queue.popleft()

        try:
            logger.info(
                f"[Crawler] [{len(results)+1}/{max_pages}] depth={depth}  {url}"
            )
            resp = requests.get(url, headers=_HEADERS, timeout=12, allow_redirects=True)

            if resp.status_code != 200:
                logger.debug(f"[Crawler] HTTP {resp.status_code} — skip {url}")
                continue
            if "text/html" not in resp.headers.get("Content-Type", ""):
                logger.debug(f"[Crawler] Non-HTML — skip {url}")
                continue

            soup  = BeautifulSoup(resp.text, "lxml")
            title = _extract_title(soup, url)
            pid   = _url_to_page_id(url)

            results.append({"page_id": pid, "url": url, "title": title})
            logger.info(f"[Crawler]   ✓  '{title}'  (id={pid})")

            if depth < max_depth:
                for link in _extract_links(soup, url):
                    if link not in visited:
                        visited.add(link)
                        queue.append((link, depth + 1))

        except requests.exceptions.Timeout:
            logger.warning(f"[Crawler] ⏱ Timeout: {url}")
        except requests.exceptions.TooManyRedirects:
            logger.warning(f"[Crawler] ↩ Redirects: {url}")
        except requests.exceptions.ConnectionError as e:
            logger.warning(f"[Crawler] 🔌 Conn error {url}: {e}")
        except Exception as e:
            logger.warning(f"[Crawler] ⚠ Error {url}: {e}")

        time.sleep(delay_seconds)

    logger.info(
        f"[Crawler] Done — collected {len(results)} pages "
        f"(visited {len(visited)} unique URLs)"
    )
    return results
