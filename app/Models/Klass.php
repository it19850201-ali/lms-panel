<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klass extends Model
{
    protected $fillable = [
        'school_id',
        'academic_level_id',
        'name',
        'moodle_course_id',
        'moodle_synced_at',
    ];

    protected $casts = [
        'moodle_synced_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicLevel()
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function students()
    {
        return $this->hasMany(User::class, 'klass_id');
    }

    public function scheduleSlots()
    {
        return $this->hasMany(ScheduleSlot::class, 'klass_id');
    }
    public function materials()
    {
        return $this->hasMany(CourseMaterial::class, 'klass_id');
    }

    /**
     * نام کامل کلاس برای نمایش در Moodle
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->academicLevel->name} - {$this->name}";
    }

    public function isSynced(): bool
    {
        return !empty($this->moodle_course_id);
    }
}
