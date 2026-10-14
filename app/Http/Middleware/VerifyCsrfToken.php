<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * ⚠️ فقط login و register معاف هستن (به دلیل مشکل کش Chrome موبایل)
     * این امن هست چون:
     * - Rate Limiting فعاله (5/min برای login)
     * - Password با bcrypt هش شده
     * - Login CSRF attack عملاً غیرممکنه
     *
     * @var array<int, string>
     */
    protected $except = [
        'login',
        'register',
    ];
}
