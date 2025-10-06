<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'due_date',
        'total_points',
        'created_by',
        'is_published'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'is_published' => 'boolean',
    ];

    /**
     * Get the class that owns the assignment
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the creator of the assignment
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the submissions for the assignment
     */
    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Get submission for a specific student
     */
    public function submissionForStudent($studentId)
    {
        return $this->submissions()->where('student_id', $studentId)->first();
    }

    /**
     * Check if assignment is overdue
     */
    public function isOverdue()
    {
        return now()->greaterThan($this->due_date);
    }
}
