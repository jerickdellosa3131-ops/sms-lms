<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassProgress extends Model
{
    use HasFactory;

    protected $table = 'class_progress';

    protected $fillable = [
        'student_id',
        'class_id',
        'modules_completed',
        'total_modules',
        'progress_percentage',
        'last_accessed'
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'last_accessed' => 'datetime',
    ];

    /**
     * Get the student that owns the progress record
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the class that owns the progress record
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Calculate progress percentage
     */
    public function calculateProgress()
    {
        if ($this->total_modules > 0) {
            $this->progress_percentage = round(
                ($this->modules_completed / $this->total_modules) * 100, 
                2
            );
        } else {
            $this->progress_percentage = 0;
        }
        
        $this->save();
        return $this->progress_percentage;
    }

    /**
     * Update last accessed time
     */
    public function updateLastAccessed()
    {
        $this->last_accessed = now();
        $this->save();
    }

    /**
     * Check if class is completed
     */
    public function isCompleted()
    {
        return $this->progress_percentage >= 100;
    }

    /**
     * Get progress status
     */
    public function getStatusAttribute()
    {
        if ($this->progress_percentage >= 100) {
            return 'completed';
        } elseif ($this->progress_percentage >= 50) {
            return 'in_progress';
        } elseif ($this->progress_percentage > 0) {
            return 'started';
        }
        return 'not_started';
    }
}
