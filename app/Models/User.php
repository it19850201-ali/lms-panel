<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'code_melli',
        'password',
        'role',
        'school_id',
        'status',
        'moodle_user_id',
        'klass_id',
        'impersonated_by',
        'password_reset_at',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_reset_at' => 'datetime',
        'password_changed_at' => 'datetime',
    ];


    // === روابط ===
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function klass()
    {
        return $this->belongsTo(Klass::class, 'klass_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // === Helper ها ===
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    /**
     * آیا کاربر رمز پیش‌فرض داره؟ (رمز = کد ملی)
     */
    public function hasDefaultPassword(): bool
    {
        if (empty($this->code_melli)) {
            return false;
        }

        return \Illuminate\Support\Facades\Hash::check($this->code_melli, $this->password);
    }

    /**
     * آیا باید هشدار تغییر رمز نشون داده بشه؟
     */
    public function shouldChangePassword(): bool
    {
        // اگه هرگز رمز عوض نکرده
        if ($this->password_changed_at === null) {
            return $this->hasDefaultPassword();
        }

        return false;
    }
}
