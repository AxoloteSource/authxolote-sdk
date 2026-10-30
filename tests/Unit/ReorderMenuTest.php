<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Clases\ReorderMenu;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ReorderMenuTest extends TestCase
{
    /** @test */
    public function it_returns_an_instance_of_reorder_menu()
    {
        $service = Authxolote::reorderMenu('menu-id', []);

        $this->assertInstanceOf(ReorderMenu::class, $service);
    }

    /** @test */
    public function it_returns_true_in_fake_mode()
    {
        Authxolote::fake();

        $result = Authxolote::reorderMenu('menu-id', [
            ['id' => 'item-1', 'parent_id' => null, 'sort_order' => 0],
        ])->run();

        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_and_captures_the_error_in_fake_mode()
    {
        Authxolote::fake();

        Authxolote::fakeResponse(ReorderMenu::class, [
            'status' => 'error',
            'message' => 'The menu item hierarchy contains a cycle.',
            'data' => [],
        ]);

        $service = Authxolote::reorderMenu('menu-id', []);
        $result = $service->run();

        $this->assertFalse($result);
        $this->assertTrue($service->hasError());
        $this->assertSame('The menu item hierarchy contains a cycle.', $service->getError()['message']);
    }

    /** @test */
    public function it_sends_the_expected_payload()
    {
        Authxolote::fake(false);

        Http::fake([
            '*/api/v1/menu-items/reorder' => Http::response([
                'status' => 'OK',
                'message' => null,
                'data' => ['reordered' => 2],
            ], 200),
        ]);

        $items = [
            ['id' => 'item-1', 'parent_id' => null, 'sort_order' => 0],
            ['id' => 'item-2', 'parent_id' => 'item-1', 'sort_order' => 0],
        ];

        $result = Authxolote::reorderMenu('menu-id', $items)->run();

        $this->assertTrue($result);

        Http::assertSent(function ($request) use ($items) {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/api/v1/menu-items/reorder')
                && $request['menu_id'] === 'menu-id'
                && $request['items'] === $items;
        });
    }

    /** @test */
    public function it_returns_false_when_the_request_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            '*/api/v1/menu-items/reorder' => Http::response([
                'status' => 'error',
                'message' => 'The payload must include every menu item of the menu.',
                'data' => null,
            ], 422),
        ]);

        $service = Authxolote::reorderMenu('menu-id', []);
        $result = $service->run();

        $this->assertFalse($result);
        $this->assertTrue($service->hasError());
        $this->assertSame('The payload must include every menu item of the menu.', $service->getError()['message']);
    }
}
