<?php

namespace Authxolote\Sdk\Middleware;

use Authxolote\Sdk\Enums\Http;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class IsAllow
{
    public function handle(Request $request, Closure $next, string $action)
    {
        if (! $request->user() || ! method_exists($request->user(), 'belongsToAction') || ! $request->user()->belongsToAction($action)) {
            if ($request->expectsJson()) {
                return Response::json([
                    'message' => __('No tienes permiso para acceder a este recurso'),
                    'data' => ['action' => $action]
                ], Http::Forbidden->value);
            }

            abort(Http::Forbidden->value, __('No tienes permiso para acceder a este recurso'));
        }

        return $next($request);
    }
}
