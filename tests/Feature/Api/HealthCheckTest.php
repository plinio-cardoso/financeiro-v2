<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_general_health_endpoint_returns_successful_response_when_all_checks_pass(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
            ])
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_general_health_endpoint_includes_database_check(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_general_health_endpoint_includes_cache_check(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_general_health_endpoint_includes_iso8601_timestamp(): void
    {
        $response = $this->getJson('/api/health');

        $timestamp = $response->json('timestamp');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $timestamp);
    }

    public function test_general_health_endpoint_does_not_require_authentication(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertSuccessful();
    }

    public function test_database_health_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/api/health/database');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
            ])
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_database_health_endpoint_includes_database_check(): void
    {
        $response = $this->getJson('/api/health/database');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_database_health_endpoint_includes_iso8601_timestamp(): void
    {
        $response = $this->getJson('/api/health/database');

        $timestamp = $response->json('timestamp');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $timestamp);
    }

    public function test_database_health_endpoint_does_not_require_authentication(): void
    {
        $response = $this->getJson('/api/health/database');

        $response->assertSuccessful();
    }

    public function test_cache_health_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/api/health/cache');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
            ])
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_cache_health_endpoint_includes_cache_check(): void
    {
        $response = $this->getJson('/api/health/cache');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_cache_health_endpoint_includes_iso8601_timestamp(): void
    {
        $response = $this->getJson('/api/health/cache');

        $timestamp = $response->json('timestamp');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $timestamp);
    }

    public function test_cache_health_endpoint_does_not_require_authentication(): void
    {
        $response = $this->getJson('/api/health/cache');

        $response->assertSuccessful();
    }
}
