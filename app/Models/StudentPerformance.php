<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPerformance extends Model
{
    use HasFactory;

    protected $table = 'student_performance';

    protected $fillable = [
        'student_id',
        'class_id',
        'attendance_rate',
        'assignment_avg',
        'quiz_avg',
        'overall_grade',
        'last_updated'
    ];

    protected $casts = [
        'attendance_rate' => 'decimal:2',
        'assignment_avg' => 'decimal:2',
        'quiz_avg' => 'decimal:2',
        'overall_grade' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    /**
     * Get the student that owns the performance record
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the class that owns the performance record
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Calculate overall grade based on components
     */
    public function calculateOverallGrade()
    {
        // Weights: Attendance 10%, Assignments 40%, Quizzes 50%
        $overall = ($this->attendance_rate * 0.1) + 
                   ($this->assignment_avg * 0.4) + 
                   ($this->quiz_avg * 0.5);
        
        $this->overall_grade = round($overall, 2);
        $this->last_updated = now();
        $this->save();
        
        return $this->overall_grade;
    }

    /**
     * Get letter grade
     */
    public function getLetterGradeAttribute()
    {
        $grade = $this->overall_grade;
        
        if ($grade >= 97) return 'A+';
        if ($grade >= 93) return 'A';
        if ($grade >= 90) return 'A-';
        if ($grade >= 87) return 'B+';
        if ($grade >= 83) return 'B';
        if ($grade >= 80) return 'B-';
        if ($grade >= 77) return 'C+';
        if ($grade >= 73) return 'C';
        if ($grade >= 70) return 'C-';
        if ($grade >= 67) return 'D+';
        if ($grade >= 60) return 'D';
        return 'F';
    }

    /**
     * Check if student is passing
     */
    public function isPassing()
    {
        return $this->overall_grade >= 60;
    }
}
