<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**
 * WebScraperService
 *
 * Dual-Mode Web Scraper & Search Service:
 * 1. Proxy Scraper API Mode: When SCRAPING_API_KEY is configured (ScraperAPI, ScrapingBee, ZenRows),
 *    routes requests through high-reputation rotating proxies to render JS and bypass anti-bot walls.
 * 2. Native Mode (Zero-API): When no key is provided, performs direct HTTP requests with DomCrawler,
 *    zero-API DuckDuckGo search, and direct Google Maps URL decoding.
 * 3. Graceful handling of blocked platforms to protect fact integrity without crashing.
 */
class WebScraperService
{
    protected string $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    protected ?string $scrapingApiKey;
    protected string $scrapingProvider;

    public function __construct()
    {
        $this->scrapingApiKey = config('services.scraper.api_key') ?: env('SCRAPING_API_KEY');
        $this->scrapingProvider = config('services.scraper.provider', 'scraperapi');
    }

    /**
     * Check if a scraping proxy API is configured.
     */
    public function hasScraperApiKey(): bool
    {
        return !empty($this->scrapingApiKey) && strlen($this->scrapingApiKey) > 5;
    }

    /**
     * Scrape a target URL using Scraper Proxy API (if configured) or native HTTP client.
     *
     * @param string $url The target website or social URL
     * @return array [status => 'success'|'scrape_restricted'|'failed', message => string, text => string, meta => array]
     */
    public function scrapeUrl(string $url): array
    {
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'status' => 'failed',
                'message' => 'Invalid URL format provided.',
                'text' => '',
                'meta' => [],
            ];
        }

        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $isSocial = $this->isSocialMediaSite($host);

        // Check if this is a Google Maps / Google Business URL (parse directly without API)
        if ($this->isGoogleMapsUrl($host, $url)) {
            return $this->parseGoogleMapsUrl($url);
        }

        // Mode 1: Proxy Scraper API (if key is configured)
        if ($this->hasScraperApiKey()) {
            $apiResult = $this->scrapeViaProxyApi($url);
            if ($apiResult['status'] === 'success') {
                return $apiResult;
            }
            // If proxy API encounters an issue, continue to native HTTP fallback below
        }

        // Mode 2: Native HTTP Client with realistic browser headers & anti-bot detection
        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(8)->get($url);

            $status = $response->status();
            $body = $response->body();

            // Detect access restrictions, anti-bot challenge walls, or login gates
            if ($status === 403 || $status === 429 || $this->isLoginRedirect($body, $url)) {
                return $this->handleScrapeRestriction($url, $host, $status);
            }

            if ($response->failed()) {
                return [
                    'status' => 'failed',
                    'message' => "HTTP {$status} error fetching {$url}",
                    'text' => '',
                    'meta' => ['status_code' => $status],
                ];
            }

            $crawler = new Crawler($body);
            $extracted = $this->extractFromHtml($crawler, $url);

            return [
                'status' => 'success',
                'message' => 'Content retrieved and parsed successfully natively.',
                'text' => $extracted['text'],
                'meta' => $extracted['meta'],
            ];

        } catch (\Throwable $e) {
            if ($isSocial) {
                return $this->handleScrapeRestriction($url, $host, 0, $e->getMessage());
            }

            return [
                'status' => 'failed',
                'message' => "Connection could not be established ({$e->getMessage()})",
                'text' => '',
                'meta' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Scrape target URL via configured proxy scraping provider (ScraperAPI, ScrapingBee, ZenRows).
     */
    protected function scrapeViaProxyApi(string $targetUrl): array
    {
        try {
            $apiUrl = '';
            $params = [];

            if ($this->scrapingProvider === 'scrapingbee') {
                $apiUrl = 'https://app.scrapingbee.com/api/v1/';
                $params = [
                    'api_key' => $this->scrapingApiKey,
                    'url' => $targetUrl,
                    'render_js' => 'true',
                ];
            } elseif ($this->scrapingProvider === 'zenrows') {
                $apiUrl = 'https://api.zenrows.com/v1/';
                $params = [
                    'apikey' => $this->scrapingApiKey,
                    'url' => $targetUrl,
                    'js_render' => 'true',
                ];
            } else {
                // Default: ScraperAPI
                $apiUrl = 'http://api.scraperapi.com';
                $params = [
                    'api_key' => $this->scrapingApiKey,
                    'url' => $targetUrl,
                    'render' => 'true',
                ];
            }

            $response = Http::timeout(15)->get($apiUrl, $params);

            if ($response->successful()) {
                $crawler = new Crawler($response->body());
                $extracted = $this->extractFromHtml($crawler, $targetUrl);

                return [
                    'status' => 'success',
                    'message' => "Successfully scraped via {$this->scrapingProvider} proxy API.",
                    'text' => $extracted['text'],
                    'meta' => array_merge($extracted['meta'], ['provider' => $this->scrapingProvider]),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("Scraper API error ({$this->scrapingProvider}): " . $e->getMessage());
        }

        return ['status' => 'failed', 'message' => 'Scraper API proxy request failed.'];
    }

    /**
     * Search the web for a business natively via DuckDuckGo (Zero API keys required).
     *
     * @param string $name Business name
     * @param string|null $city Business city/locality
     * @return array [query => string, results => array, candidates => array, is_ambiguous => bool, engine => string]
     */
    public function searchBusiness(string $name, ?string $city = null): array
    {
        $query = trim($name . ($city ? " {$city}" : ""));

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->timeout(6)->get('https://html.duckduckgo.com/html/', ['q' => $query]);

            if ($response->successful()) {
                $crawler = new Crawler($response->body());
                $results = [];

                $crawler->filter('.result')->each(function (Crawler $node) use (&$results) {
                    if (count($results) >= 5) return;
                    $title = $node->filter('.result__title')->count() ? trim($node->filter('.result__title')->text()) : '';
                    $snippet = $node->filter('.result__snippet')->count() ? trim($node->filter('.result__snippet')->text()) : '';
                    $url = $node->filter('.result__url')->count() ? trim($node->filter('.result__url')->text()) : '';

                    if ($title && $snippet) {
                        $results[] = [
                            'title' => $title,
                            'snippet' => $snippet,
                            'url' => 'https://' . ltrim($url, '/'),
                        ];
                    }
                });

                $candidates = $this->identifyCandidateBusinesses($results, $name, $city);

                return [
                    'query' => $query,
                    'results' => $results,
                    'candidates' => $candidates,
                    'is_ambiguous' => count($candidates) > 1,
                    'engine' => 'duckduckgo',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("DuckDuckGo search error: " . $e->getMessage());
        }

        return [
            'query' => $query,
            'results' => [],
            'candidates' => [],
            'is_ambiguous' => false,
            'engine' => 'none',
        ];
    }

    /**
     * Check if a URL points to Google Maps or Google Business listing.
     */
    protected function isGoogleMapsUrl(string $host, string $url): bool
    {
        return str_contains($host, 'google.') && (str_contains($url, '/maps') || str_contains($url, 'maps.app.goo.gl'));
    }

    /**
     * Parse Google Maps URL natively without Google Cloud API keys.
     * Extracts place name, coordinates, and location queries directly from the link structure.
     */
    public function parseGoogleMapsUrl(string $url): array
    {
        $placeName = '';
        $coords = null;

        // Pattern 1: /maps/place/Business+Name/@lat,lng,zoom
        if (preg_match('#/maps/place/([^/@?]+)(?:/@([0-9.-]+,[0-9.-]+))?#', $url, $matches)) {
            $placeName = urldecode(str_replace('+', ' ', $matches[1]));
            $coords = $matches[2] ?? null;
        } elseif (preg_match('/[?&]q=([^&]+)/', $url, $matches)) {
            // Pattern 2: ?q=Business+Name+Address
            $placeName = urldecode(str_replace('+', ' ', $matches[1]));
        }

        $meta = [
            'url' => $url,
            'title' => $placeName ?: 'Google Business Listing',
            'place_name' => $placeName,
            'coordinates' => $coords,
            'is_google_business' => true,
        ];

        $text = "Google Business Listing: " . ($placeName ?: $url) . "\n";
        if ($coords) {
            $text .= "Coordinates: {$coords}\n";
        }
        $text .= "Verified Google Business Profile link provided by business owner.";

        return [
            'status' => 'success',
            'message' => 'Google Business listing details extracted natively from URL.',
            'text' => $text,
            'meta' => $meta,
        ];
    }

    /**
     * Check if a hostname belongs to a known social media walled-garden.
     */
    protected function isSocialMediaSite(string $host): bool
    {
        $socials = ['instagram.com', 'facebook.com', 'fb.com', 'linkedin.com', 'tiktok.com', 'twitter.com', 'x.com'];
        $lowerHost = strtolower($host);
        foreach ($socials as $social) {
            if (str_contains($lowerHost, $social)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the HTML body represents a login gate, bot challenge, or redirect.
     */
    protected function isLoginRedirect(string $body, string $url): bool
    {
        $bodyLower = strtolower($body);
        return str_contains($bodyLower, 'login • instagram') || 
               str_contains($bodyLower, 'log into facebook') ||
               str_contains($bodyLower, 'sign in | linkedin') ||
               str_contains($bodyLower, 'enable javascript and cookies to continue') ||
               (str_contains($bodyLower, 'cloudflare') && str_contains($bodyLower, 'challenge'));
    }

    /**
     * Gracefully handle scrape restrictions from social platforms.
     */
    protected function handleScrapeRestriction(string $url, string $host, int $status = 0, ?string $extra = null): array
    {
        $platform = 'Social Media';
        if (str_contains($host, 'instagram')) $platform = 'Instagram';
        elseif (str_contains($host, 'facebook') || str_contains($host, 'fb.com')) $platform = 'Facebook';
        elseif (str_contains($host, 'linkedin')) $platform = 'LinkedIn';
        elseif (str_contains($host, 'tiktok')) $platform = 'TikTok';

        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $handle = explode('/', $path)[0] ?? '';

        $message = "{$platform} restricted automated scraping for {$url}. " .
                   ($handle ? "Identified handle: @{$handle}. " : "") .
                   "To guarantee accurate facts, please review your business details in the editor.";

        return [
            'status' => 'scrape_restricted',
            'platform' => $platform,
            'message' => $message,
            'text' => "[Verified {$platform} Link: {$url}" . ($handle ? " (Handle: @{$handle})" : "") . " - Automated extraction restricted by {$platform}]",
            'meta' => [
                'platform' => $platform,
                'handle' => $handle,
                'url' => $url,
                'status_code' => $status,
                'requires_user_input' => true,
            ],
        ];
    }

    /**
     * Extract structured fields and clean text from an HTML document.
     */
    protected function extractFromHtml(Crawler $crawler, string $url): array
    {
        $meta = [
            'url' => $url,
            'title' => '',
            'description' => '',
            'og_image' => '',
            'phone' => null,
            'email' => null,
            'address' => null,
        ];

        try {
            $meta['title'] = $crawler->filter('title')->count() ? trim($crawler->filter('title')->text()) : '';
        } catch (\Throwable $e) {}

        try {
            $descNode = $crawler->filter('meta[name="description"], meta[property="og:description"]');
            if ($descNode->count()) {
                $meta['description'] = trim($descNode->attr('content') ?? '');
            }
        } catch (\Throwable $e) {}

        try {
            $imgNode = $crawler->filter('meta[property="og:image"]');
            if ($imgNode->count()) {
                $meta['og_image'] = trim($imgNode->attr('content') ?? '');
            }
        } catch (\Throwable $e) {}

        $html = $crawler->html();
        if (preg_match('/(?:\+?1[-.\s]?)?\(?[0-9]{3}\)?[-.\s]?[0-9]{3}[-.\s]?[0-9]{4}/', $html, $phoneMatch)) {
            $meta['phone'] = trim($phoneMatch[0]);
        }

        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $html, $emailMatch)) {
            $meta['email'] = trim($emailMatch[0]);
        }

        $bodyText = '';
        try {
            $cleaned = clone $crawler;
            $cleaned->filter('script, style, noscript, svg')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode?->removeChild($node);
                }
            });

            $bodyText = $cleaned->filter('body')->count() ? $cleaned->filter('body')->text() : '';
            $bodyText = preg_replace('/\s+/', ' ', $bodyText);
            $bodyText = mb_substr(trim($bodyText), 0, 8000);
        } catch (\Throwable $e) {
            $bodyText = $meta['title'] . ' ' . $meta['description'];
        }

        $formatted = "Title: {$meta['title']}\nDescription: {$meta['description']}\n";
        if ($meta['phone']) {
            $formatted .= "Phone found: {$meta['phone']}\n";
        }
        if ($meta['email']) {
            $formatted .= "Email found: {$meta['email']}\n";
        }
        $formatted .= "Content excerpt:\n" . mb_substr($bodyText, 0, 3000);

        return [
            'text' => $formatted,
            'meta' => $meta,
        ];
    }

    /**
     * Identify distinct business candidates to resolve ambiguous name duplicates.
     */
    protected function identifyCandidateBusinesses(array $results, string $name, ?string $city): array
    {
        $candidates = [];
        $lowerName = strtolower($name);

        foreach ($results as $res) {
            $lowerTitle = strtolower($res['title']);
            if (str_contains($lowerTitle, $lowerName)) {
                $candidates[] = [
                    'title' => $res['title'],
                    'snippet' => $res['snippet'],
                    'url' => $res['url'],
                    'location_hint' => $city ?? 'Unknown location',
                ];
            }
        }

        return $candidates;
    }
}
