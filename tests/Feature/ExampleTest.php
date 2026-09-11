<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /*
     * Laravel ships this with RefreshDatabase commented out, which is fine for
     * a skeleton whose "/" is static. This app's welcome page counts projects,
     * so without a schema the test failed on every run and the suite was never
     * green — a permanently red suite is one nobody reads.
     */
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
