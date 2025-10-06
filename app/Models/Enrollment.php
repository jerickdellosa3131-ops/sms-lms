<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'enrollment_date',
        'status'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
    ];

    /**
     * Get the student that owns the enrollment
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the class that owns the enrollment
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Check if enrollment is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if enrollment is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if enrollment is dropped
     */
    public function isDropped()
    {
        return $this->status === 'dropped';
    }
}
