<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403);
        }

        $user = auth()->user();

        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'دسترسی فقط برای مدیر مدرسه');
        }

        return $next($request);
    }
}
