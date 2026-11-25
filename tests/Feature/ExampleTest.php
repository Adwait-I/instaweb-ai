<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test the root URL redirects to the website builder dashboard.
     */
    public function test_the_application_redirects_to_builder(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('builder.index'));
    }
}
