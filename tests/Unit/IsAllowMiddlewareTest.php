<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\ExternalUser;
use Authxolote\Sdk\Middleware\IsAllow;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

class IsAllowMiddlewareTest extends TestCase
{
    /** @test */
    public function it_allows_access_if_user_has_permission()
    {
        $user = new ExternalUser([
            'data' => [
                'user' => [
                    'id' => '123',
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role_id' => 'admin-role',
                    'updated_at' => now()->toIso8601String(),
                    'created_at' => now()->toIso8601String(),
                    'remember_token' => null,
                ]
            ]
        ]);

        Authxolote::actingAs($user);
        Authxolote::actionsFake(['edit-posts']);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new IsAllow();
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'edit-posts');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_denies_access_if_user_does_not_have_permission()
    {
        $user = new ExternalUser([
            'data' => [
                'user' => [
                    'id' => '123',
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role_id' => 'admin-role',
                    'updated_at' => now()->toIso8601String(),
                    'created_at' => now()->toIso8601String(),
                    'remember_token' => null,
                ]
            ]
        ]);

        Authxolote::actingAs($user);
        Authxolote::actionsFake(['other-action']);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new IsAllow();

        try {
            $middleware->handle($request, function ($req) {
                return response('OK');
            }, 'edit-posts');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
            $this->assertEquals('No tienes permiso para acceder a este recurso', $e->getMessage());
            return;
        }

        $this->fail('Expected 403 Forbidden exception was not thrown.');
    }

    /** @test */
    public function it_returns_json_response_for_json_requests_when_denied()
    {
        $user = new ExternalUser([
            'data' => [
                'user' => [
                    'id' => '123',
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role_id' => 'admin-role',
                    'updated_at' => now()->toIso8601String(),
                    'created_at' => now()->toIso8601String(),
                    'remember_token' => null,
                ]
            ]
        ]);

        Authxolote::actingAs($user);
        Authxolote::actionsFake([]);

        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);

        $middleware = new IsAllow();
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'edit-posts');

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('No tienes permiso para acceder a este recurso', $data['message']);
        $this->assertEquals('edit-posts', $data['data']['action']);
    }
}
