<?php

namespace Tests\Feature;

use App\Services\WebScraperService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScraperAndSearchApiTest extends TestCase
{
    protected function tearDown(): void
    {
        Config::set('services.scraper.api_key', null);
        parent::tearDown();
    }
    /**
     * Test proxy scraper API mode when SCRAPING_API_KEY is configured.
     */
    public function test_scrape_via_scraping_proxy_api(): void
    {
        Config::set('services.scraper.api_key', 'test_scraper_api_key_123');
        Config::set('services.scraper.provider', 'scraperapi');

        Http::fake([
            'http://api.scraperapi.com*' => Http::response(
                '<html><head><title>Pizzeria Austin</title><meta name="description" content="Woodfired pizza"></head><body><h1>Welcome</h1><p>Phone: (512) 555-7890</p></body></html>',
                200
            ),
        ]);

        $service = new WebScraperService();
        $result = $service->scrapeUrl('https://instagram.com/pizzeria_austin');

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Pizzeria Austin', $result['meta']['title']);
        $this->assertEquals('(512) 555-7890', $result['meta']['phone']);
        $this->assertEquals('scraperapi', $result['meta']['provider']);
    }

    /**
     * Test native URL scraping extracting title, description, phone, email and body text.
     */
    public function test_native_url_scraping_without_external_api(): void
    {
        Config::set('services.scraper.api_key', null);

        Http::fake([
            'https://austinbakery.com*' => Http::response(
                '<!DOCTYPE html>
                <html>
                <head>
                    <title>Austin Artisan Bakery</title>
                    <meta name="description" content="Fresh sourdough and pastries in downtown Austin.">
                </head>
                <body>
                    <h1>Welcome to Our Bakery</h1>
                    <p>Call us directly at (512) 555-4321 or email orders@austinbakery.com</p>
                    <p>Open daily from 7:00 AM to 3:00 PM.</p>
                </body>
                </html>',
                200
            ),
        ]);

        $service = new WebScraperService();
        $result = $service->scrapeUrl('https://austinbakery.com');

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Austin Artisan Bakery', $result['meta']['title']);
        $this->assertEquals('Fresh sourdough and pastries in downtown Austin.', $result['meta']['description']);
        $this->assertEquals('(512) 555-4321', $result['meta']['phone']);
        $this->assertEquals('orders@austinbakery.com', $result['meta']['email']);
        $this->assertStringContainsString('Fresh sourdough', $result['text']);
    }

    /**
     * Test native DuckDuckGo search and ambiguous candidate detection.
     */
    public function test_native_duckduckgo_search_and_ambiguity_detection(): void
    {
        Http::fake([
            'https://html.duckduckgo.com/html/*' => Http::response(
                '<html><body>
                    <div class="result">
                        <a class="result__title" href="//duckduckgo.com/l/?uddg=https%3A%2F%2Fbellanapoliaustin.com">Bella Napoli Austin - Downtown</a>
                        <div class="result__snippet">Authentic Italian pizzeria located on 408 Congress Ave.</div>
                        <a class="result__url" href="bellanapoliaustin.com">bellanapoliaustin.com</a>
                    </div>
                    <div class="result">
                        <a class="result__title" href="//duckduckgo.com/l/?uddg=https%3A%2F%2Fsouth.bellanapoliaustin.com">Bella Napoli Pizzeria South</a>
                        <div class="result__snippet">Second branch located on South Lamar Blvd, Austin.</div>
                        <a class="result__url" href="south.bellanapoliaustin.com">south.bellanapoliaustin.com</a>
                    </div>
                </body></html>',
                200
            ),
        ]);

        $service = new WebScraperService();
        $result = $service->searchBusiness('Bella Napoli', 'Austin');

        $this->assertEquals('duckduckgo', $result['engine']);
        $this->assertCount(2, $result['results']);
        $this->assertTrue($result['is_ambiguous']);
    }

    /**
     * Test Google Maps URL parsing directly from link structure (Zero API keys needed).
     */
    public function test_parse_google_maps_url_without_api(): void
    {
        $service = new WebScraperService();
        $mapsUrl = 'https://www.google.com/maps/place/Bella+Napoli+Trattoria/@30.2672,-97.7431,17z';

        $result = $service->scrapeUrl($mapsUrl);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Bella Napoli Trattoria', $result['meta']['place_name']);
        $this->assertEquals('30.2672,-97.7431', $result['meta']['coordinates']);
        $this->assertTrue($result['meta']['is_google_business']);
    }

    /**
     * Test social site blocks handled gracefully without crashing.
     */
    public function test_social_platform_restriction_handling(): void
    {
        Config::set('services.scraper.api_key', null);

        $service = new WebScraperService();
        $result = $service->scrapeUrl('https://instagram.com/local_business_profile');

        $this->assertEquals('scrape_restricted', $result['status']);
        $this->assertEquals('Instagram', $result['platform']);
        $this->assertTrue($result['meta']['requires_user_input']);
        $this->assertStringContainsString('restricted automated scraping', $result['message']);
    }
}
