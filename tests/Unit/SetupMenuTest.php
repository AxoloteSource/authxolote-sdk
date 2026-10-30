<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Clases\SetupMenu;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SetupMenuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Authxolote::fake(false);
    }

    /** @test */
    public function it_returns_an_instance_of_setup_menu()
    {
        $service = Authxolote::setupMenuAction(['name' => 'Test Menu']);

        $this->assertInstanceOf(SetupMenu::class, $service);
    }

    /** @test */
    public function it_sends_force_false_by_default()
    {
        Http::fake([
            '*/api/v1/menus/setup' => Http::response(['status' => 'OK'], 201),
        ]);

        $result = Authxolote::setupMenuAction(['name' => 'Test Menu'])->run();

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/api/v1/menus/setup')
                && $request['name'] === 'Test Menu'
                && $request['force'] === false;
        });
    }

    /** @test */
    public function it_sends_force_true_when_requested()
    {
        Http::fake([
            '*/api/v1/menus/setup' => Http::response(['status' => 'OK'], 201),
        ]);

        $result = Authxolote::setupMenuAction(['name' => 'Test Menu'], true)->run();

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request['name'] === 'Test Menu'
                && $request['force'] === true;
        });
    }

    /** @test */
    public function it_returns_false_and_captures_the_error_when_the_request_fails()
    {
        Http::fake([
            '*/api/v1/menus/setup' => Http::response([
                'status' => 'error',
                'message' => 'The menu item type ":type" is invalid for ":slug".',
                'data' => [],
            ], 422),
        ]);

        $service = Authxolote::setupMenuAction(['name' => 'Test Menu'], true);
        $result = $service->run();

        $this->assertFalse($result);
        $this->assertTrue($service->hasError());
    }
}
