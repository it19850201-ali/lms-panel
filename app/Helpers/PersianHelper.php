<?php

namespace App\Helpers;

class PersianHelper
{
    /**
     * تبدیل اعداد فارسی/عربی به انگلیسی
     */
    public static function digitsToEnglish(?string $value): ?string
    {
        if ($value === null) return null;

        return str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹', '٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9', '0','1','2','3','4','5','6','7','8','9'],
            $value
        );
    }

    /**
     * تبدیل اعداد انگلیسی به فارسی (برای نمایش)
     */
    public static function digitsToPersian(?string $value): ?string
    {
        if ($value === null) return null;

        return str_replace(
            ['0','1','2','3','4','5','6','7','8','9'],
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
            $value
        );
    }

    /**
     * نرمال‌سازی نام برای مقایسه (اعداد انگلیسی + حذف فاصله اضافی)
     */
    public static function normalizeName(?string $name): ?string
    {
        if ($name === null) return null;

        $name = self::digitsToEnglish($name);
        $name = preg_replace('/\s+/', ' ', trim($name));

        return $name;
    }
}
