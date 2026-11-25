<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\WebsiteSection;
use App\Services\TruthLensService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TruthLensAndFlyerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test TruthLens calculates grounding integrity and matches claims to sources.
     */
    public function test_truth_lens_audits_claims_and_calculates_integrity_score(): void
    {
        $business = Business::create([
            'name' => 'Trattoria Bella',
            'city' => 'Austin, TX',
            'slug' => 'trattoria-bella-austin',
            'phone' => '(512) 555-0199',
            'email' => 'ciao@trattoriabella.com',
            'address' => '408 Congress Ave',
        ]);

        BusinessSource::create([
            'business_id' => $business->id,
            'type' => 'file',
            'title_or_filename' => 'Menu_Summer_2026.pdf',
            'extracted_text' => 'Trattoria Bella Margherita Pizza $18.00 408 Congress Ave (512) 555-0199',
        ]);

        WebsiteSection::create([
            'business_id' => $business->id,
            'section_key' => 'services',
            'section_type' => 'services_grid',
            'title' => 'Wood-Fired Pizza',
            'content' => [
                'items' => [
                    ['name' => 'Margherita Pizza', 'price' => '$18.00', 'category' => 'Pizza']
                ]
            ],
            'order_index' => 1,
            'is_visible' => true,
        ]);

        $truthLens = new TruthLensService();
        $audit = $truthLens->auditBusiness($business);

        $this->assertIsArray($audit);
        $this->assertGreaterThanOrEqual(90, $audit['fact_integrity_score']);
        $this->assertEquals('ZERO / MINIMAL', $audit['hallucination_risk']);
        $this->assertNotEmpty($audit['contact_claims']);
        $this->assertNotEmpty($audit['section_breakdown']);
    }

    /**
     * Test QR Code generation returns valid SVG markup.
     */
    public function test_qr_code_generation_returns_svg(): void
    {
        $truthLens = new TruthLensService();
        $svg = $truthLens->generateQrCodeSvg('http://localhost:8000/site/trattoria-bella-austin');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    /**
     * Test Printable Storefront Launch Flyer endpoint renders successfully.
     */
    public function test_storefront_flyer_endpoint_renders_with_qr_code(): void
    {
        $business = Business::create([
            'name' => 'Apex Performance Care',
            'city' => 'Denver, CO',
            'slug' => 'apex-performance-denver',
            'phone' => '(303) 555-9988',
            'address' => '2100 Broadway, Denver, CO',
            'category' => 'Auto Repair',
            'tagline' => 'Denver’s Trusted European Auto Specialist',
            'opening_hours' => 'Mon–Fri: 8am–6pm',
        ]);

        $response = $this->get(route('builder.flyer', $business->slug));

        $response->assertStatus(200);
        $response->assertSee('Apex Performance Care');
        $response->assertSee('Denver, CO');
        $response->assertSee('Scan with Phone Camera');
        $response->assertSee('<svg', false); // Contains SVG QR code
    }

    /**
     * Test TruthLens JSON Audit API endpoint.
     */
    public function test_truth_lens_audit_api_endpoint(): void
    {
        $business = Business::create([
            'name' => 'Metro Smile Dental',
            'city' => 'Atlanta, GA',
            'slug' => 'metro-smile-dental',
            'phone' => '(404) 555-4321',
        ]);

        $response = $this->getJson(route('builder.audit', $business->slug));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'audit' => [
                'fact_integrity_score',
                'verified_claims_count',
                'hallucination_risk',
                'contact_claims',
            ]
        ]);
    }
}
