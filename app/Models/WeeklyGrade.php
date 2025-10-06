<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'week_number',
        'academic_year',
        'assignment_score',
        'quiz_score',
        'status'
    ];

    protected $casts = [
        'assignment_score' => 'decimal:2',
        'quiz_score' => 'decimal:2',
    ];

    /**
     * Get the student that owns the weekly grade
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Calculate average score for the week
     */
    public function getAverageScoreAttribute()
    {
        $scores = array_filter([
            $this->assignment_score,
            $this->quiz_score
        ]);

        if (empty($scores)) {
            return 0;
        }

        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Check if week is graded
     */
    public function isGraded()
    {
        return $this->status === 'graded';
    }
}
