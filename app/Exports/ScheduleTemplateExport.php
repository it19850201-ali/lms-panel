<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScheduleTemplateExport implements
    FromArray,
    WithHeadings,
    WithColumnWidths,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected int $emptyRows = 200;

    public function array(): array
    {
        // ۳ ردیف نمونه
        $sample = [
            ['هفتم', '۷۰۱', 'شنبه',   'زنگ اول',   'علی مرادیان', '09192991584', 'ریاضی', '101'],
            ['هفتم', '۷۰۱', 'شنبه',   'زنگ دوم',   'زهرا احمدی',  '09121111111', 'علوم',  '101'],
            ['هشتم', '۸۰۱', 'یکشنبه', 'زنگ اول',   'محمد رضایی',  '09122222222', 'فارسی', '102'],
        ];

        // ۲۰۰ ردیف خالی
        $empty = array_fill(0, $this->emptyRows, ['', '', '', '', '', '', '', '']);

        return array_merge($sample, $empty);
    }

    public function headings(): array
    {
        return [
            'پایه',
            'کلاس',
            'روز',
            'زنگ',
            'نام معلم',
            'موبایل معلم',
            'درس',
            'اتاق',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 10,
            'C' => 12,
            'D' => 12,
            'E' => 20,
            'F' => 16,
            'G' => 20,
            'H' => 10,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT, // موبایل
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = count($this->array()) + 1;

                $sheet->getStyle("F2:F{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);

                $sheet->getStyle("A1:H1")->getAlignment()->setHorizontal('center');
                $sheet->freezePane('A2');
            },
        ];
    }
}
