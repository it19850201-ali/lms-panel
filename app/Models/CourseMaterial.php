<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseMaterial extends Model
{
    protected $fillable = [
        'klass_id',
        'teacher_id',
        'subject',
        'title',
        'description',
        'category',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
    ];

    public static array $categories = ['جزوه', 'تکلیف', 'ویدیو', 'سایر'];

    public static array $allowedExtensions = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        'mp4', 'mp3', 'wav', 'avi', 'mov',
        'zip', 'rar', 'txt', 'csv',
    ];

    public function klass()
    {
        return $this->belongsTo(Klass::class, 'klass_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    public function getIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));

        return match (true) {
            $ext === 'pdf' => '📕',
            in_array($ext, ['doc', 'docx']) => '📘',
            in_array($ext, ['xls', 'xlsx', 'csv']) => '📗',
            in_array($ext, ['ppt', 'pptx']) => '📙',
            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']) => '🖼️',
            in_array($ext, ['mp4', 'avi', 'mov']) => '🎬',
            in_array($ext, ['mp3', 'wav']) => '🎵',
            in_array($ext, ['zip', 'rar']) => '🗜️',
            default => '📄',
        };
    }
}
