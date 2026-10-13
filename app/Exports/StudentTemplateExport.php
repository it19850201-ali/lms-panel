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

class StudentTemplateExport implements
    FromArray,
    WithHeadings,
    WithColumnWidths,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected int $emptyRows = 50;

    public function array(): array
    {
        // ۳ ردیف نمونه
        $sample = [
            ['علی', 'علوی', '0102030405', '09124545454'],
            ['زهرا', 'احمدی',   '008888888', '09121020304'],
            ['محمد', 'رضایی',   '0073214784', '09126070809'],
        ];

        // ۵۰ ردیف خالی
        $empty = array_fill(0, $this->emptyRows, ['', '', '', '']);

        return array_merge($sample, $empty);
    }

    public function headings(): array
    {
        return ['نام', 'نام خانوادگی', 'کد ملی', 'موبایل'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 18,
            'C' => 16,
            'D' => 16,
        ];
    }

    /**
     * ستون‌های C و D به‌صورت Text (برای حفظ صفر اول)
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
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

                $sheet->getStyle("C2:C{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);

                $sheet->getStyle("D2:D{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);

                $sheet->getStyle("A1:D1")->getAlignment()->setHorizontal('center');
                $sheet->freezePane('A2');
            },
        ];
    }
}
