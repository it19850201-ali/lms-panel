<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // اجبار HTTPS
        if (config('app.env') === 'production' || !app()->isLocal()) {
            URL::forceScheme('https');
        }

        // Blade directive: @jalali($date)
        // مثال: @jalali($log->created_at)  یا  @jalali($log->created_at, 'Y/m/d H:i')
        Blade::directive('jalali', function ($expression) {
            return "<?php echo \App\Helpers\JalaliHelper::toJalali({$expression}); ?>";
        });

        // Blade directive: @jalaliDate($date) - فقط تاریخ
        Blade::directive('jalaliDate', function ($expression) {
            return "<?php echo \App\Helpers\JalaliHelper::toJalali({$expression}, 'Y/m/d'); ?>";
        });

        // Blade directive: @jalaliTime($date) - فقط ساعت
        Blade::directive('jalaliTime', function ($expression) {
            return "<?php echo \App\Helpers\JalaliHelper::toJalali({$expression}, 'H:i'); ?>";
        });
    }
}
