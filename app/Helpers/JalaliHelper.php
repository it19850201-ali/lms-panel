<?php

namespace App\Helpers;

use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class JalaliHelper
{
    /**
     * میلادی → شمسی
     */
    public static function toJalali($date, string $format = 'Y/m/d H:i'): string
    {
        if (empty($date)) return '—';

        try {
            if (is_string($date)) {
                $date = Carbon::parse($date);
            }

            return Jalalian::fromDateTime($date)->format($format);
        } catch (\Throwable $e) {
            return '—';
        }
    }

    /**
     * شمسی → میلادی
     */
    public static function toGregorian(?string $jalaliDate): ?Carbon
    {
        if (empty($jalaliDate)) return null;

        try {
            $jalaliDate = trim($jalaliDate);

            // تبدیل اعداد فارسی
            $jalaliDate = str_replace(
                ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
                ['0','1','2','3','4','5','6','7','8','9'],
                $jalaliDate
            );

            $jalaliDate = str_replace('-', '/', $jalaliDate);

            if (!preg_match('/\d{1,2}:\d{2}/', $jalaliDate)) {
                $jalaliDate .= ' 00:00';
            }

            [$date, $time] = explode(' ', $jalaliDate);
            [$y, $m, $d] = explode('/', $date);
            [$h, $i] = explode(':', $time);

            return (new Jalalian((int)$y, (int)$m, (int)$d, (int)$h, (int)$i))->toCarbon();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * فاصله‌ی زمانی به فارسی (مثلاً «۳ روز پیش»)
     */
    public static function diffForHumans($date): string
    {
        if (empty($date)) return '—';

        try {
            if (is_string($date)) {
                $date = Carbon::parse($date);
            }

            return Jalalian::fromDateTime($date)->ago();
        } catch (\Throwable $e) {
            return '—';
        }
    }

    /**
     * نام روز هفته به فارسی
     */
    public static function dayOfWeek($date): string
    {
        if (empty($date)) return '—';

        try {
            if (is_string($date)) {
                $date = Carbon::parse($date);
            }

            return Jalalian::fromDateTime($date)->format('%A');
        } catch (\Throwable $e) {
            return '—';
        }
    }
}
