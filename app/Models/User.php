<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'users';
    
    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'user_type',
        'first_name',
        'last_name',
        'middle_name',
        'profile_picture',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the password for authentication
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Relationships
     */
    public function student()
    {
        return $this->hasOne(Student::class, 'user_id', 'user_id');
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id', 'user_id');
    }

    public function personalInfo()
    {
        return $this->hasOne(PersonalInfo::class, 'user_id', 'user_id');
    }

    /**
     * Scope queries
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAdmin($query)
    {
        return $query->where('user_type', USER_TYPE_ADMIN);
    }

    public function scopeTeacher($query)
    {
        return $query->where('user_type', USER_TYPE_TEACHER);
    }

    public function scopeStudent($query)
    {
        return $query->where('user_type', USER_TYPE_STUDENT);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->user_type === USER_TYPE_ADMIN;
    }

    /**
     * Check if user is teacher
     */
    public function isTeacher()
    {
        return $this->user_type === USER_TYPE_TEACHER;
    }

    /**
     * Check if user is student
     */
    public function isStudent()
    {
        return $this->user_type === USER_TYPE_STUDENT;
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
    }
}
