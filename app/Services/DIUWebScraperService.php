<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * DIUWebScraperService
 *
 * Fetches live content from official DIU websites and caches it for 6 hours.
 *
 * Two fetch strategies:
 *  1. JSON API  — for the admission announce-notice endpoint (real deadline data)
 *  2. HTML scrape — for other pages (general info, facilities, etc.)
 *
 * Fallback: returns empty string per source on failure so RAGService
 * can gracefully fall back to its static knowledge base.
 */
class DIUWebScraperService
{
    /** Cache duration in seconds (6 hours). */
    protected int $cacheTtl = 21600;

    /** Maximum characters to keep per HTML-scraped source. */
    protected int $maxCharsPerSource = 800;

    /**
     * Browser-like headers required to avoid 403 on the DIU backend API.
     * The Referer header is critical — the API CORS policy checks it.
     */
    protected array $browserHeaders = [
        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept'          => 'application/json, text/plain, */*',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Referer'         => 'https://daffodilvarsity.edu.bd/',
        'Origin'          => 'https://daffodilvarsity.edu.bd',
    ];

    /**
     * HTML pages to scrape for general DIU info.
     * Only includes pages that don't have equivalent structured JSON APIs.
     */
    protected array $htmlSources = [
        'DIU Main Website'   => 'https://daffodilvarsity.edu.bd',
        'Scholarship Info'   => 'https://daffodilvarsity.edu.bd/scholarship/diu-scholarship',
        'Departments'        => 'https://daffodilvarsity.edu.bd/departments',
        'Campus Facilities'  => 'https://dsc.creative-bd.com/',
        'News & Events'      => 'https://news.daffodilvarsity.edu.bd/',
    ];

    /**
     * DIU backend JSON API endpoints (reverse-engineered from the Next.js bundles).
     * These return structured JSON data with actual live content.
     *
     * Base: https://webbackend.daffodilvarsity.edu.bd/api/v1/public/
     */
    protected array $jsonApis = [
        'Admission Notices & Deadlines' => 'https://webbackend.daffodilvarsity.edu.bd/api/v1/public/admission-announce-notice',
        'Tuition Fees (All Programs)'   => 'https://webbackend.daffodilvarsity.edu.bd/api/v1/public/tuition-fees',
        'Transport Routes'              => 'https://webbackend.daffodilvarsity.edu.bd/api/v1/public/transport',
        'Lab Facilities (API)'          => 'https://webbackend.daffodilvarsity.edu.bd/api/v1/public/lab-facilities',
    ];

    /**
     * Fetch all sources and return an associative array:
     *   [ 'Label' => 'clean text content', ... ]
     */
    public function fetchAllSources(): array
    {
        $results = [];

        // 1. JSON API sources (most accurate — structured data)
        foreach ($this->jsonApis as $label => $url) {
            $results[$label] = $this->fetchJsonApi($label, $url);
        }

        // 2. HTML scrape sources (general context)
        foreach ($this->htmlSources as $label => $url) {
            $results[$label] = $this->fetchHtmlSource($label, $url);
        }

        return $results;
    }

    // =========================================================================
    // JSON API FETCHING
    // =========================================================================

    /**
     * Fetch a JSON API endpoint (cached 6h). Returns formatted plain text.
     */
    public function fetchJsonApi(string $label, string $url): string
    {
        $cacheKey = 'diu_api_' . md5($url);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($label, $url) {
            return $this->callJsonApi($label, $url);
        });
    }

    /**
     * Perform the JSON API call and convert the response to clean readable text.
     */
    protected function callJsonApi(string $label, string $url): string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->browserHeaders)
                ->get($url);

            if (!$response->successful()) {
                Log::warning("[DIUScraper/API] Failed '{$label}': HTTP {$response->status()} — {$url}");
                return '';
            }

            $data = $response->json();

            if (empty($data)) {
                return '';
            }

            $text = $this->formatJsonResponse($data, $label);

            Log::info("[DIUScraper/API] ✓ Fetched '{$label}' — " . strlen($text) . " chars");

            return $text;
        } catch (\Exception $e) {
            Log::warning("[DIUScraper/API] Exception '{$label}': " . $e->getMessage());
            return '';
        }
    }

    /**
     * Format a JSON response into clean, human-readable text.
     * Automatically handles pagination/nesting, grouping, dates, and currency.
     */
    protected function formatJsonResponse(array $data, string $label): string
    {
        // 1. Locate the actual list data
        $items = $data['tuitions'] ?? $data['data'] ?? $data['notices'] ?? $data;
        if (!is_array($items)) {
            return is_string($items) ? $items : '';
        }

        // If it's not a list of items (associative array representing a single item), wrap it
        if (!empty($items) && !isset($items[0])) {
            $items = [$items];
        }

        if (empty($items)) {
            return '';
        }

        $lines = ["## {$label} (Live Data)"];
        if (!empty($data['message']) && is_string($data['message'])) {
            $lines[] = "Source Info: " . $data['message'];
        }
        $lines[] = "";

        // 2. Determine if we should group by a key (e.g. faculty_name or department_name)
        $groupByKey = null;
        $firstItem = $items[0] ?? [];
        if (is_array($firstItem)) {
            foreach (['faculty_name', 'department_name'] as $candidate) {
                if (isset($firstItem[$candidate])) {
                    $groupByKey = $candidate;
                    break;
                }
            }
        }

        // Group items if grouping key is found
        $grouped = [];
        if ($groupByKey) {
            foreach ($items as $item) {
                if (!is_array($item)) continue;
                $groupVal = $item[$groupByKey] ?? 'Other';
                $grouped[$groupVal][] = $item;
            }
        } else {
            $grouped[''] = $items;
        }

        $hiddenKeys = ['id', 'created_at', 'updated_at', 'slug', 'image', 'status', 'department_id', 'faculty_id', 'tuition_category_id', 'program_id', 'maximum_credit', 'maximum_tuition_fees', 'maximum_total_fees', 'publication_status', 'faculty_short_name', 'department_name'];

        foreach ($grouped as $groupName => $groupItems) {
            if ($groupName !== '') {
                $lines[] = "### {$groupName}";
                $lines[] = "";
            }

            foreach ($groupItems as $item) {
                if (!is_array($item)) {
                    if (is_string($item)) {
                        $lines[] = "- {$item}";
                    }
                    continue;
                }

                // Identify the main title/header for this item
                $itemTitleKeys = ['notice_title', 'program_name', 'route_name', 'name', 'title'];
                $itemTitle = null;
                foreach ($itemTitleKeys as $tk) {
                    if (!empty($item[$tk]) && is_string($item[$tk]) && $tk !== $groupByKey) {
                        $itemTitle = $item[$tk];
                        break;
                    }
                }

                if ($itemTitle) {
                    $lines[] = "**{$itemTitle}**";
                }

                // Format the rest of the fields
                foreach ($item as $key => $value) {
                    if ($value === null || $value === '' || in_array($key, $hiddenKeys) || $key === $groupByKey) {
                        continue;
                    }
                    // Don't repeat the title key if we printed it as the main bold item title
                    if ($itemTitle && $value === $itemTitle) {
                        continue;
                    }

                    if (is_array($value)) {
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }

                    // Format dates (matches YYYY-MM-DD)
                    if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        $formatted = \DateTime::createFromFormat('Y-m-d', $value);
                        if ($formatted) {
                            $value = $formatted->format('F j, Y');
                        }
                    }

                    // Format currency / numbers for fee-related fields
                    if (is_scalar($value) && is_numeric(str_replace(',', '', $value)) && preg_match('/fee|cost|price|total|admission|semester/i', $key)) {
                        $cleanNum = (int) str_replace(',', '', $value);
                        $value = "৳" . number_format($cleanNum) . " BDT";
                    }

                    $humanKey = ucwords(str_replace(['_', '-'], ' ', $key));
                    $lines[] = "- {$humanKey}: {$value}";
                }

                // Include status if present, at the end
                if (isset($item['status']) && $item['status'] !== '') {
                    $lines[] = "- Status: {$item['status']}";
                }

                $lines[] = "";
            }
        }

        $text = implode("\n", $lines);
        $text = trim($text);

        // Truncate based on the label/source to optimize token footprint
        $limit = match ($label) {
            'Tuition Fees (All Programs)'   => 6000,
            'Admission Notices & Deadlines' => 1500,
            default                         => 1000,
        };

        return strlen($text) > $limit ? substr($text, 0, $limit) . "\n…(truncated)" : $text;
    }

    // =========================================================================
    // HTML SCRAPING
    // =========================================================================

    /**
     * Fetch and scrape an HTML page (cached 6h).
     */
    public function fetchHtmlSource(string $label, string $url): string
    {
        $cacheKey = 'diu_scraper_' . md5($url);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($label, $url) {
            return $this->scrapeUrl($label, $url);
        });
    }

    /**
     * Kept for backward-compat — resolves to fetchHtmlSource().
     */
    public function fetchSource(string $label, string $url): string
    {
        return $this->fetchHtmlSource($label, $url);
    }

    /**
     * Perform the HTTP scrape request and clean HTML to plain text.
     */
    protected function scrapeUrl(string $label, string $url): string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(array_merge($this->browserHeaders, [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ]))
                ->get($url);

            if (!$response->successful()) {
                Log::warning("[DIUScraper/HTML] Failed '{$label}': HTTP {$response->status()} — {$url}");
                return '';
            }

            $html = $response->body();
            $text = $this->cleanHtml($html);

            Log::info("[DIUScraper/HTML] ✓ Fetched '{$label}' — " . strlen($text) . " chars from {$url}");

            return $text;
        } catch (\Exception $e) {
            Log::warning("[DIUScraper/HTML] Exception '{$label}': " . $e->getMessage() . " — {$url}");
            return '';
        }
    }

    /**
     * Strip HTML tags, decode entities, collapse whitespace, and truncate.
     */
    protected function cleanHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (strlen($text) > $this->maxCharsPerSource) {
            $text = substr($text, 0, $this->maxCharsPerSource) . '…';
        }

        return $text;
    }

    // =========================================================================
    // CACHE WARMING
    // =========================================================================

    /**
     * Pre-warm the cache for all sources. Returns [ 'label' => 'ok'|'failed' ].
     */
    public function warmCache(): array
    {
        $report = [];

        // JSON API sources
        foreach ($this->jsonApis as $label => $url) {
            Cache::forget('diu_api_' . md5($url));
            $text = $this->fetchJsonApi($label, $url);
            $report[$label] = !empty(trim($text)) ? 'ok' : 'failed';
        }

        // HTML sources
        foreach ($this->htmlSources as $label => $url) {
            Cache::forget('diu_scraper_' . md5($url));
            $text = $this->fetchHtmlSource($label, $url);
            $report[$label] = !empty(trim($text)) ? 'ok' : 'failed';
        }

        return $report;
    }
}
