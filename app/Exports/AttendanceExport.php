<?php

namespace App\Exports;

use App\Models\ScheduleSlot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $logs;
    protected int $rowNumber = 0;

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            'ردیف',
            'تاریخ',
            'روز',
            'پایه',
            'کلاس',
            'زنگ',
            'ساعت زنگ',
            'نام',
            'نقش',
            'ساعت ورود',
            'ساعت خروج',
            'مدت حضور (دقیقه)',
        ];
    }

    public function map($log): array
    {
        $this->rowNumber++;

        $slot = $log->scheduleSlot ?? null;
        $klass = $slot?->klass;
        $bell = $slot?->bell;

        // روز هفته
        $dayName = '—';
        if ($log->session_date) {
            $carbonDay = $log->session_date->dayOfWeek;
            $jalaliDay = $carbonDay === 6 ? 0 : $carbonDay + 1;
            $dayName = ScheduleSlot::$days[$jalaliDay] ?? '—';
        }

        // نقش
        $roleLabel = $log->isModerator() ? 'معلم' : 'دانش‌آموز';

        // مدت حضور
        try {
            $duration = $log->calculateDuration();
        } catch (\Throwable $e) {
            $duration = 0;
        }

        // زنگ و ساعت
        $bellName = '—';
        $bellTime = '—';

        if ($bell) {
            $bellName = $bell->name;
            $bellTime = $bell->start_time_short . ' - ' . $bell->end_time_short;
        } elseif ($slot && $slot->is_extra) {
            $bellName = 'فوق‌برنامه';
            $bellTime = $slot->time_label;
        }

        return [
            $this->rowNumber,
            $log->session_date?->format('Y-m-d') ?? '—',
            $dayName,
            $klass?->academicLevel?->name ?? '—',
            $klass?->name ?? '—',
            $bellName,
            $bellTime,
            $log->full_name ?? '—',
            $roleLabel,
            $log->first_joined_at?->format('H:i') ?? '—',
            $log->left_at?->format('H:i') ?? 'حاضر',
            $duration,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EEF2FF'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(1, $this->rowNumber + 1);

                $sheet->getStyle("A1:L{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                $sheet->getStyle("A1:L{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $sheet->freezePane('A2');

                $sheet->getStyle("H2:H{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('right');
            },
        ];
    }
}
