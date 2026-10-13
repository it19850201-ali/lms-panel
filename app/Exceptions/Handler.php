<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // مدیریت خطای 419 (CSRF Token Mismatch)
        $this->renderable(function (TokenMismatchException $e, $request) {
            // اگه درخواست Ajax/Fetch باشه
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'نشست شما منقضی شده است. لطفاً صفحه را رفرش کنید.',
                ], 419);
            }

            // برگرد به landing با پیام
            return redirect()->route('home')
                ->with('info', 'نشست شما منقضی شد. لطفاً دوباره وارد شوید.');
        });
    }
}
