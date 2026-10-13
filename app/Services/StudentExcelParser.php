<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class StudentExcelParser
{
    /**
     * فایل اکسل رو می‌خونه و آرایه‌ای از ['name' => ..., 'email' => ...] برمی‌گردونه.
     * فرض: ستون A = نام، ستون B = ایمیل، ردیف اول = هدر (نادیده گرفته می‌شه)
     */
    public static function parse(UploadedFile $file): array
    {
        $rows = Excel::toArray([], $file)[0]; // شیت اول

        $students = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // رد کردن ردیف هدر

            $name = trim($row[0] ?? '');
            $email = trim($row[1] ?? '');

            if (empty($name) || empty($email)) continue;

            $students[] = ['name' => $name, 'email' => $email];
        }

        return $students;
    }
}
