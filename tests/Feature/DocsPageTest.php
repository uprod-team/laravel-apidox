<?php

namespace PrivateEvent\Apidox\Tests\Feature;

use Illuminate\Support\Facades\Route;
use PrivateEvent\Apidox\Services\RouteScanner;
use PrivateEvent\Apidox\Tests\TestCase;

class DocsPageTest extends TestCase
{
    public function test_documentation_page_responds(): void
    {
        $this->get('/developers')
            ->assertOk()
            ->assertSee('Documentation développeurs');
    }

    public function test_css_asset_is_served(): void
    {
        $response = $this->get('/vendor/apidox/apidox.css');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/css; charset=UTF-8');
        $this->assertStringContainsString('apidox-method-get', $response->getContent());
    }

    public function test_scanner_finds_registered_api_routes(): void
    {
        Route::get('api/v1/widgets', fn () => [])->name('widgets.index');
        Route::post('api/v1/widgets', fn () => [])->name('widgets.store');

        $endpoints = app(RouteScanner::class)->scan();
        $names = collect($endpoints)->pluck('name')->all();

        $this->assertContains('widgets.index', $names);
        $this->assertContains('widgets.store', $names);
    }

    public function test_scanner_ignores_non_api_routes(): void
    {
        Route::get('admin/dashboard', fn () => [])->name('admin.dashboard');

        $endpoints = app(RouteScanner::class)->scan();
        $uris = collect($endpoints)->pluck('uri')->all();

        $this->assertNotContains('/admin/dashboard', $uris);
    }

    public function test_scanner_extracts_phpdoc_summary(): void
    {
        Route::get('api/v1/things', [\PrivateEvent\Apidox\Tests\Fixtures\ThingsController::class, 'index']);

        $endpoints = app(RouteScanner::class)->scan();
        $thing = collect($endpoints)->firstWhere('uri', '/api/v1/things');

        $this->assertNotNull($thing);
        $this->assertEquals('List all the things.', $thing['summary']);
    }
}
