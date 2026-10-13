<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'user_id',
        'moodle_course_id',
        'bbb_meeting_id',
        'title',
        'type',
        'meta',
	'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'meta' => 'array',
	'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
