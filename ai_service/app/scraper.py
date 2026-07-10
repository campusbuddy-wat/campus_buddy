"""
scraper.py
──────────
WHY:    The AI only knows what we give it. This file is the "harvester" —
        it visits every official DIU web page and extracts the clean,
        human-readable text so we can store it in our vector database.

WHAT:   Defines a list of official DIU URLs to scrape and a function for
        each source category. Returns dicts with 'page_id', 'url',
        'title', and 'text'.

HOW:    Uses requests to download HTML, BeautifulSoup to parse it, and
        strips away all navigation/header/footer noise so we only keep
        the main content. An MD5 hash of the text is stored to detect
        changes on future scrape runs (skip if unchanged).

NEVER:  Scrapes non-official sources. Every URL must be from
        daffodilvarsity.edu.bd or its official subdomains.
"""

import hashlib
import logging
import time
from typing import Dict, List, Optional, Union

import requests
from bs4 import BeautifulSoup

from app.crawler import crawl as crawl_website

logger = logging.getLogger(__name__)

# ── API-only sources (JSON — Hybrid API-first strategy) ──────────────────────────
# Rule: Official JSON API always preferred over HTML scraping.
# Crawler is fallback only for pages with no API.
API_SOURCES: List[Dict] = [
    # ─ Fees ────────────────────────────────────────────────
    {"page_id": "tuition_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/tuition-fees",
     "title":   "Tuition Fees (Domestic / Bangladeshi Students)",
     "is_api":  True, "category": "fees"},
    {"page_id": "tuition_api_international",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/tuition-fees?tuition_category_id=2",
     "title":   "Tuition Fees (International Students - USD)",
     "is_api":  True, "category": "fees"},
    # ─ Admission ──────────────────────────────────────────────
    {"page_id": "admission_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/admission-announce-notice",
     "title":   "Admission Notices",
     "is_api":  True, "category": "admission"},
    {"page_id": "exam_schedule_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/article-details?slug=admission-test-schedule",
     "title":   "Admission Test Schedule",
     "is_api":  True, "category": "admission"},
    # ─ Academic ──────────────────────────────────────────────
    {"page_id": "programs_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/academic/programs",
     "title":   "Academic Programs and Departments",
     "is_api":  True, "category": "academic"},
    {"page_id": "faculties_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/academic/faculties",
     "title":   "Faculties",
     "is_api":  True, "category": "academic"},
    # ─ Scholarships ────────────────────────────────────────────
    {"page_id": "scholarship_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/accordion/scholarship",
     "title":   "Scholarships and Waivers (Domestic)",
     "is_api":  True, "category": "scholarship"},
    {"page_id": "scholarship_int_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/scholarship/scholarship-int?content_type=content",
     "title":   "Scholarships for International Students",
     "is_api":  True, "category": "scholarship"},
    # ─ Campus Life ────────────────────────────────────────────
    {"page_id": "transport_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v2/public/transport",
     "title":   "Transport Routes and Schedule",
     "is_api":  True, "category": "campus"},
    {"page_id": "notice_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/notice?per_page=20&page=1",
     "title":   "Notice Board",
     "is_api":  True, "category": "notice"},
    {"page_id": "life_insurance_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/article-details?slug=life-insurance-for-student-and-guardian",
     "title":   "Life Insurance for Students and Guardians",
     "is_api":  True, "category": "campus"},
    # ─ International ───────────────────────────────────────────
    {"page_id": "int_advisor_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/article-details?slug=international-adviser",
     "title":   "International Adviser",
     "is_api":  True, "category": "international"},
    {"page_id": "int_members_api",
     "url":     "https://webbackend.daffodilvarsity.edu.bd/api/v1/public/mps/members",
     "title":   "International Memberships and Partnerships",
     "is_api":  True, "category": "international"},
]

# Browser-like headers so university servers don't block us
HEADERS: Dict = {
    "User-Agent": (
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/120.0.0.0 Safari/537.36"
    ),
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "Accept-Language": "en-US,en;q=0.9",
}

# HTML elements to remove (navigation, ads, etc.)
NOISE_TAGS: list[str] = [
    "script", "style", "nav", "footer", "header",
    "aside", "form", "iframe", "noscript", "button", "svg",
]

# Max characters to keep per page (prevents huge pages from flooding tokens)
MAX_PAGE_CHARS: int = 100_000


def _md5(text: str) -> str:
    """Return an MD5 hash of the text — used to detect page changes."""
    return hashlib.md5(text.encode("utf-8")).hexdigest()


def _clean_html(html: str) -> str:
    """
    Parse raw HTML, remove noisy elements, and return clean plain text.
    Preserves paragraph structure by keeping newlines.
    """
    soup = BeautifulSoup(html, "lxml")

    # Remove noise tags
    for tag in soup(NOISE_TAGS):
        tag.decompose()

    # Get text, using newline as separator between blocks
    text = soup.get_text(separator="\n", strip=True)

    # Collapse many blank lines into a single blank line
    lines = [line.strip() for line in text.splitlines()]
    cleaned_lines = []
    prev_blank = False
    for line in lines:
        if not line:
            if not prev_blank:
                cleaned_lines.append("")
            prev_blank = True
        else:
            cleaned_lines.append(line)
            prev_blank = False

    return "\n".join(cleaned_lines)[:MAX_PAGE_CHARS]


def _flatten_tuition_fees(data: Union[dict, list], title: str, is_international: bool = False) -> str:
    """
    Specialized formatter for the DIU tuition-fees API.
    Outputs each program as a clearly labelled block so the LLM cannot
    miss or misread the OFFICIAL TOTAL PROGRAM COST.
    Handles both domestic (BDT) and international (USD) fee structures.
    """
    currency = "USD $" if is_international else "BDT "
    lines = [f"# {title}", ""]
    if is_international:
        lines.append("Note: All fees are in US Dollars (USD). These are for INTERNATIONAL STUDENTS only.")
        lines.append("")

    programs = data if isinstance(data, list) else []

    for prog in programs:
        if not isinstance(prog, dict):
            continue

        name     = prog.get("program_name", "Unknown Program")
        dept     = prog.get("department_name", "")
        faculty  = prog.get("faculty_name", "")
        ptype    = prog.get("program_type", "")
        duration = prog.get("program_duration", "")
        credit   = prog.get("credit", "")
        majors   = prog.get("majors", "")

        # Fee fields — read exactly from API (pre-calculated by university)
        admission = prog.get("admission_fees", 0)
        semester  = prog.get("semester_cost", 0)
        tuition   = prog.get("tuition_fees", 0)
        total     = prog.get("total_fees", 0)    # ← OFFICIAL pre-calculated total
        other_fee = prog.get("other_fee", 0)

        # Year-by-year fees (present in international structure)
        yr1 = prog.get("first_year")
        yr2 = prog.get("second_year")
        yr3 = prog.get("third_year")
        yr4 = prog.get("fourth_year")

        # Skip records with no useful data
        if not name or not total:
            continue

        lines.append(f"## {name}")
        if dept:     lines.append(f"Department: {dept}")
        if faculty:  lines.append(f"Faculty: {faculty}")
        if ptype:    lines.append(f"Type: {ptype}")
        if duration: lines.append(f"Duration: {duration}")
        if credit:   lines.append(f"Total Credit: {credit}")
        if majors:   lines.append(f"Majors: {majors}")
        lines.append("")
        lines.append("Fee Breakdown:")
        lines.append(f"  Admission Fees:              {currency}{admission}")
        lines.append(f"  Semester Cost:               {currency}{semester}")
        lines.append(f"  Tuition Fees:                {currency}{tuition}")
        if other_fee:
            lines.append(f"  Other Fees:                  {currency}{other_fee}")
        lines.append(f"  TOTAL PROGRAM COST (official): {currency}{total}")
        lines.append("")
        # Year-by-year breakdown if available
        if any(v for v in [yr1, yr2, yr3, yr4]):
            lines.append("Year-by-Year Payment Schedule:")
            if yr1: lines.append(f"  1st Year (during admission): {currency}{yr1}")
            if yr2: lines.append(f"  2nd Year tuition & reg fees: {currency}{yr2}")
            if yr3: lines.append(f"  3rd Year tuition & reg fees: {currency}{yr3}")
            if yr4: lines.append(f"  4th Year tuition & reg fees: {currency}{yr4}")
            lines.append("")
        lines.append("---")
        lines.append("")

    text = "\n".join(lines)
    return text[:MAX_PAGE_CHARS]


def _flatten_api_json(data: Union[dict, list], title: str) -> str:
    """
    Generic fallback formatter for any JSON API response.
    Handles nested dicts/lists and strips internal fields.
    """
    lines = [f"# {title}\n"]

    def _walk(obj, depth=0):
        indent = "  " * depth
        if isinstance(obj, dict):
            for key, val in obj.items():
                # Skip internal/system fields
                if key.lower() in {"id", "created_at", "updated_at", "slug",
                                   "image", "status", "publication_status",
                                   "faculty_id", "department_id", "program_id",
                                   "maximum_credit", "maximum_tuition_fees",
                                   "maximum_total_fees", "tuition_category_id",
                                   "cover_picture", "faculty_photo", "faculty_serial",
                                   "rules_policy_pdf", "map_url"}:
                    continue
                if isinstance(val, (dict, list)):
                    lines.append(f"{indent}{key.replace('_', ' ').title()}:")
                    _walk(val, depth + 1)
                elif val not in (None, "", 0):
                    human_key = key.replace("_", " ").title()
                    lines.append(f"{indent}{human_key}: {val}")
        elif isinstance(obj, list):
            for item in obj:
                _walk(item, depth)
                lines.append("")    # blank line between items

    _walk(data)
    text = "\n".join(lines)
    return text[:MAX_PAGE_CHARS]


# ── Specialized formatters for each new API category ─────────────────────────

def _strip_html(html: str) -> str:
    """Quick HTML tag stripper using BeautifulSoup."""
    if not html:
        return ""
    return BeautifulSoup(html, "lxml").get_text(separator=" ", strip=True)


def _fmt_programs(data: dict, title: str) -> str:
    """Format the academic programs & departments API."""
    lines = [f"# {title}", ""]
    depts = data.get("departments", [])
    lines.append(f"Total Departments: {len(depts)}")
    lines.append("")
    for d in depts:
        name   = d.get("department_name", "")
        short  = d.get("short_name", "")
        fac    = d.get("faculty_name", "")
        about  = _strip_html(d.get("about", ""))[:300]
        lines.append(f"## {name} ({short.upper()})")
        if fac:   lines.append(f"Faculty: {fac}")
        if about: lines.append(f"About: {about}")
        lines.append("")
    return "\n".join(lines)[:MAX_PAGE_CHARS]


def _fmt_scholarship(data: dict, title: str) -> str:
    """Format the scholarship accordion API."""
    lines = [f"# {title}", ""]
    top = _strip_html(data.get("top_content", ""))[:500]
    if top:
        lines.append(top)
        lines.append("")
    for acc in data.get("accordions", []):
        lines.append(f"## {acc.get('title', '')}")
        desc = _strip_html(acc.get("description", ""))[:600]
        if desc:
            lines.append(desc)
        lines.append("")
    return "\n".join(lines)[:MAX_PAGE_CHARS]


def _fmt_transport(data: dict, title: str) -> str:
    """Format transport routes and schedules."""
    lines = [f"# {title}"]
    lines.append("## Official Transport Fee Structures:")
    lines.append("- Tri-Semester Subscription Fee: BDT 3,000 per semester (unlimited rides)")
    lines.append("- Bi-Semester Subscription Fee: BDT 4,500 per semester (unlimited rides)")
    lines.append("- Single Ride Ticket (Standard Fare): BDT 20 per ride")
    lines.append("- Single Ride Ticket (Discounted with Student ID card): BDT 15 per ride")
    lines.append("")
    lines.append(f"Semester: {data.get('semester', '')}")
    lines.append(f"Total Roads: {data.get('total_roads', '')}")
    lines.append("")

    all_roads = data.get("data", {})
    for road_type, roads in all_roads.items():
        if not isinstance(roads, list) or not roads:
            continue
        type_label = road_type.replace("_", " ").title()
        lines.append(f"## {type_label}")
        for road in roads:
            lines.append(f"### Route: {road.get('name', '')}")
            lines.append(f"  From: {road.get('start_stop', '')}")
            lines.append(f"  To:   {road.get('end_stop', '')}")
            stops = road.get("stops", [])
            if stops:
                stop_names = [s.get("name", "") for s in stops]
                lines.append(f"  Stops: {' → '.join(stop_names)}")
            schedules = road.get("schedules", {})
            from_home = [s.get("time", "")[:5] for s in schedules.get("from_home", [])]
            from_campus = [s.get("time", "")[:5] for s in schedules.get("from_campus", [])]
            if from_home:   lines.append(f"  Departure times (to campus): {', '.join(from_home)}")
            if from_campus: lines.append(f"  Return times (from campus):  {', '.join(from_campus)}")
            lines.append("")
    return "\n".join(lines)[:MAX_PAGE_CHARS]


def _fmt_notices(data: list, title: str) -> str:
    """Format notice board items."""
    lines = [f"# {title}", ""]
    for n in data:
        ntitle = n.get("title", "")
        cat    = n.get("notice_category", "")
        date   = n.get("create_at", "")
        files  = n.get("noticeFiles", [])
        lines.append(f"## {ntitle}")
        if cat:  lines.append(f"Category: {cat}")
        if date: lines.append(f"Date: {date}")
        if files:
            for f in files:
                furl = f.get("file", "")
                if furl: lines.append(f"File: {furl}")
        lines.append("")
    return "\n".join(lines)[:MAX_PAGE_CHARS]


def _fmt_article(data: dict, title: str) -> str:
    """Format a generic article/article-details API response."""
    lines = [f"# {title}", ""]
    art_title = data.get("title", "")
    if art_title and art_title != title:
        lines.append(f"## {art_title}")
    content = _strip_html(data.get("content", "") or data.get("short_content", ""))
    if content:
        lines.append(content)
    return "\n".join(lines)[:MAX_PAGE_CHARS]


def _fmt_int_members(data: dict, title: str) -> str:
    """Format international memberships and partnerships."""
    lines = [f"# {title}", ""]
    members = data.get("mps", [])
    lines.append(f"Total memberships: {len(members)}")
    lines.append("")
    # Group by category
    by_cat: dict = {}
    for m in members:
        cat = m.get("category_name", "Other")
        by_cat.setdefault(cat, []).append(m)
    for cat, mems in by_cat.items():
        lines.append(f"## {cat}")
        for m in mems:
            lines.append(f"  - {m.get('title', '')} | {m.get('url', '')}")
        lines.append("")
    return "\n".join(lines)[:MAX_PAGE_CHARS]


def _fmt_scholarship_int(data, title: str) -> str:
    """Format international scholarship content."""
    lines = [f"# {title}", ""]
    if isinstance(data, list):
        for item in data:
            if isinstance(item, dict):
                item_title = item.get("title", "")
                content = _strip_html(item.get("content", ""))[:800]
                if item_title:
                    lines.append(f"## {item_title}")
                if content:
                    lines.append(content)
                lines.append("")
    elif isinstance(data, dict):
        content = _strip_html(data.get("content", ""))[:1000]
        if content:
            lines.append(content)
    return "\n".join(lines)[:MAX_PAGE_CHARS]


# ── Routing map: page_id → formatter function ─────────────────────────────────
_API_FORMATTERS = {
    "tuition_api":             lambda d, t: _flatten_tuition_fees(d, t, is_international=False),
    "tuition_api_international": lambda d, t: _flatten_tuition_fees(d, t, is_international=True),
    "programs_api":            _fmt_programs,
    "faculties_api":           lambda d, t: _fmt_programs({"departments": d if isinstance(d, list) else []}, t),
    "scholarship_api":         _fmt_scholarship,
    "scholarship_int_api":     _fmt_scholarship_int,
    "transport_api":           _fmt_transport,
    "notice_api":              _fmt_notices,
    "exam_schedule_api":       _fmt_article,
    "life_insurance_api":      _fmt_article,
    "int_advisor_api":         _fmt_article,
    "int_members_api":         _fmt_int_members,
}


def scrape_source(source: dict) -> Optional[dict]:
    """
    Scrape a single source dict and return:
    {
        'page_id':      str,
        'url':          str,
        'title':        str,
        'category':     str,   # e.g. 'fees', 'academic', 'campus'
        'text':         str,   # clean plain text
        'hash':         str,   # MD5 hash of text (for change detection)
    }
    Returns None if the request fails.
    """
    from datetime import date
    url      = source["url"]
    title    = source["title"]
    is_api   = source.get("is_api", False)
    category = source.get("category", "general")
    page_id  = source["page_id"]

    try:
        logger.info(f"[Scraper] Fetching: {title} ({url})")
        resp = requests.get(url, headers=HEADERS, timeout=15)
        resp.raise_for_status()

        if is_api:
            raw  = resp.json()
            # Unwrap common envelope keys — some APIs need the full raw dict
            # because their formatter reads from root-level fields too
            _no_unwrap = {"programs_api", "transport_api"}
            data = raw
            if isinstance(raw, dict) and page_id not in _no_unwrap:
                for key in ("tuitions", "data", "notices", "results"):
                    if key in raw:
                        data = raw[key]
                        break

            # Route to the correct specialized formatter
            formatter = _API_FORMATTERS.get(page_id)
            if formatter:
                text = formatter(data, title)
            else:
                text = _flatten_api_json(data, title)
        else:
            text = _clean_html(resp.text)

        if not text.strip():
            logger.warning(f"[Scraper] Empty text for {title}, skipping.")
            return None

        return {
            "page_id":  page_id,
            "url":      url,
            "title":    title,
            "category": category,
            "text":     text,
            "hash":     _md5(text),
        }

    except Exception as e:
        logger.error(f"[Scraper] Failed to scrape {title}: {e}")
        return None


def scrape_all(delay_seconds: float = 1.0) -> list[dict]:
    """
    Full pipeline:
    1. Run the BFS crawler to discover all HTML pages on daffodilvarsity.edu.bd
    2. Scrape each discovered HTML page
    3. Scrape the hardcoded API_SOURCES (JSON endpoints)
    4. Return all results combined

    Args:
        delay_seconds: Pause between HTTP requests (politeness).

    Returns:
        List of {page_id, url, title, text, hash} dicts ready for the embedder.
    """
    # ── Step 1: Discover HTML pages via crawler ───────────────────────────────
    logger.info("[Scraper] Starting crawler to discover all DIU pages...")
    discovered_html = crawl_website()          # uses config.CRAWL_START_URL etc.
    logger.info(f"[Scraper] Crawler found {len(discovered_html)} HTML pages.")

    # ── Step 2: Combine with API sources ─────────────────────────────────────
    all_sources = discovered_html + API_SOURCES
    logger.info(f"[Scraper] Total sources to scrape: {len(all_sources)} "
                f"({len(discovered_html)} HTML + {len(API_SOURCES)} API)")

    # ── Step 3: Scrape each source ────────────────────────────────────────────
    results = []
    for source in all_sources:
        result = scrape_source(source)
        if result:
            results.append(result)
        time.sleep(delay_seconds)

    logger.info(
        f"[Scraper] Scraped {len(results)}/{len(all_sources)} sources successfully."
    )
    return results
