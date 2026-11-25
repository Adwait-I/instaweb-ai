<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiKeySettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting API key status.
     */
    public function test_get_api_key_status(): void
    {
        $response = $this->getJson(route('builder.settings.api_key.status'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'configured',
            'masked_key',
            'model',
        ]);
    }

    /**
     * Test saving API key with mock response.
     */
    public function test_save_api_key_validates_and_tests_connectivity(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'pong']]]]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('builder.settings.api_key.save'), [
            'api_key' => 'AIzaSyFakeKeyForTesting1234567890',
            'model' => 'gemini-2.5-flash',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * Test saving invalid API key fails gracefully with error message.
     */
    public function test_save_api_key_handles_invalid_key_gracefully(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'API key not valid. Please pass a valid API key.']
            ], 400),
        ]);

        $response = $this->postJson(route('builder.settings.api_key.save'), [
            'api_key' => 'AIzaSyInvalidKeyTesting1234567890',
            'model' => 'gemini-2.5-flash',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
