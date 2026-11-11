<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klass extends Model
{
    protected $fillable = [
        'school_id',
        'academic_level_id',
        'name',
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

    public function getFullNameAttribute(): string
    {
        return "{$this->academicLevel->name} - {$this->name}";
    }
}
