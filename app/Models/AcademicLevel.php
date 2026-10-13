<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicLevel extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'order',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function klasses()
    {
        return $this->hasMany(Klass::class);
    }

    public function students()
    {
        return $this->hasManyThrough(User::class, Klass::class, 'academic_level_id', 'klass_id', 'id', 'id');
    }
}
