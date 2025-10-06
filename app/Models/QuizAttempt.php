<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'student_id',
        'attempt_number',
        'started_at',
        'completed_at',
        'score',
        'total_possible',
        'status',
        'answers'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2',
        'answers' => 'array',
    ];

    /**
     * Get the quiz that owns the attempt
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the student that owns the attempt
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Calculate percentage score
     */
    public function getPercentageAttribute()
    {
        if ($this->total_possible > 0) {
            return round(($this->score / $this->total_possible) * 100, 2);
        }
        return 0;
    }

    /**
     * Check if attempt is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Get duration of attempt in minutes
     */
    public function getDurationAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return null;
    }
}
