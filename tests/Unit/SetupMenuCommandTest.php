<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SetupMenuCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Authxolote::fake(false);

        config()->set('authxolote.menu', [
            'name' => 'Test Menu',
            'application' => [
                'name' => 'Test App',
                'slug' => 'test-app',
            ],
            'menu_items' => [],
        ]);
    }

    /** @test */
    public function it_sets_up_the_menu_without_confirmation_when_force_is_not_used()
    {
        Http::fake([
            '*/api/v1/menus/setup' => Http::response(['status' => 'OK'], 201),
        ]);

        $this->artisan('authxolote:menu')->assertExitCode(0);

        Http::assertSent(function ($request) {
            return $request['name'] === 'Test Menu'
                && $request['force'] === false;
        });
    }

    /** @test */
    public function it_asks_for_confirmation_and_sends_force_when_accepted()
    {
        Http::fake([
            '*/api/v1/menus/setup' => Http::response(['status' => 'OK'], 201),
        ]);

        $this->artisan('authxolote:menu', ['--force' => true])
            ->expectsConfirmation('¿Deseas continuar?', 'yes')
            ->assertExitCode(0);

        Http::assertSent(function ($request) {
            return $request['name'] === 'Test Menu'
                && $request['force'] === true;
        });
    }

    /** @test */
    public function it_does_not_call_the_api_when_the_confirmation_is_declined()
    {
        Http::fake();

        $this->artisan('authxolote:menu', ['--force' => true])
            ->expectsConfirmation('¿Deseas continuar?', 'no')
            ->assertExitCode(1);

        Http::assertNothingSent();
    }

    /** @test */
    public function it_aborts_when_the_menu_is_not_configured()
    {
        config()->set('authxolote.menu', []);

        Http::fake();

        $this->artisan('authxolote:menu')->assertExitCode(1);

        Http::assertNothingSent();
    }

    /** @test */
    public function it_returns_an_error_when_the_api_rejects_the_setup()
    {
        Http::fake([
            '*/api/v1/menus/setup' => Http::response(['status' => 'error'], 422),
        ]);

        $this->artisan('authxolote:menu')->assertExitCode(1);
    }
}
