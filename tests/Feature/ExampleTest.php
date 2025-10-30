<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test to ensure the API responds.
     * We will test that an unknown API route returns a 404 Not Found status.
     * This proves that the API routing is active.
     */
    public function test_the_api_returns_a_not_found_response_for_unknown_routes(): void
    {
        // We act as if we are requesting a JSON response.
        $response = $this->getJson('/api/this-route-does-not-exist');

        // The correct behavior for an unknown API route is to return 404.
        $response->assertStatus(404);
    }
}
