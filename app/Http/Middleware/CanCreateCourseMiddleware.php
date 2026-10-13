<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanCreateCourseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403);
        }

        $user = auth()->user();

        // فقط ادمین کل و مدیر مدرسه می‌تونن کلاس بسازن
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'فقط مدیران می‌توانند کلاس بسازند. شما باید توسط مدیر مدرسه به کلاس‌ها اختصاص داده شوید.');
        }

        return $next($request);
    }
}
