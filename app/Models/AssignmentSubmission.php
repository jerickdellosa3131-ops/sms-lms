<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_date',
        'file_path',
        'comments',
        'status',
        'grade',
        'feedback',
        'graded_at',
        'graded_by'
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'graded_at' => 'datetime',
        'grade' => 'decimal:2',
    ];

    /**
     * Get the assignment that owns the submission
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student that owns the submission
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the grader (teacher)
     */
    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Check if submission is late
     */
    public function isLate()
    {
        return $this->submission_date && 
               $this->assignment && 
               $this->submission_date->greaterThan($this->assignment->due_date);
    }

    /**
     * Check if submission is graded
     */
    public function isGraded()
    {
        return $this->status === 'graded';
    }
}
