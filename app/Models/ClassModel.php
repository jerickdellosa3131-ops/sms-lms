<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'code',
        'description',
        'teacher_id',
        'semester',
        'academic_year',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the teacher that owns the class
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the enrollments for the class
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    /**
     * Get the students enrolled in the class
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'class_id', 'user_id')
                    ->withPivot('enrollment_date', 'status')
                    ->withTimestamps();
    }

    /**
     * Get the assignments for the class
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    /**
     * Get the quizzes for the class
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'class_id');
    }

    /**
     * Get the lesson materials for the class
     */
    public function lessonMaterials()
    {
        return $this->hasMany(LessonMaterial::class, 'class_id');
    }

    /**
     * Get the announcements for the class
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'class_id');
    }

    /**
     * Get the progress tracking for the class
     */
    public function classProgress()
    {
        return $this->hasMany(ClassProgress::class, 'class_id');
    }
}
