<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'duration_minutes',
        'total_points',
        'available_from',
        'available_until',
        'max_attempts',
        'shuffle_questions',
        'show_results',
        'created_by',
        'is_published'
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'shuffle_questions' => 'boolean',
        'show_results' => 'boolean',
        'is_published' => 'boolean',
    ];

    /**
     * Get the class that owns the quiz
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the creator of the quiz
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attempts for the quiz
     */
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Check if quiz is available now
     */
    public function isAvailable()
    {
        return now()->between($this->available_from, $this->available_until);
    }

    /**
     * Get attempts for a specific student
     */
    public function attemptsForStudent($studentId)
    {
        return $this->attempts()->where('student_id', $studentId)->get();
    }

    /**
     * Get best score for a student
     */
    public function bestScoreForStudent($studentId)
    {
        return $this->attempts()
                    ->where('student_id', $studentId)
                    ->where('status', 'completed')
                    ->max('score');
    }
}
