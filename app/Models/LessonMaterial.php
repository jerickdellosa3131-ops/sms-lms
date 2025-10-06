<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'file_type',
        'file_path',
        'file_size',
        'uploaded_by',
        'visible_to_students',
        'download_count'
    ];

    protected $casts = [
        'visible_to_students' => 'boolean',
    ];

    /**
     * Get the class that owns the lesson material
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the uploader (teacher)
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        if ($this->file_size < 1024) {
            return $this->file_size . ' KB';
        }

        return round($this->file_size / 1024, 2) . ' MB';
    }

    /**
     * Get file icon based on type
     */
    public function getFileIconAttribute()
    {
        return match($this->file_type) {
            'pdf' => 'bi-file-pdf',
            'pptx' => 'bi-file-ppt',
            'docx' => 'bi-file-word',
            'mp4' => 'bi-file-play',
            'url' => 'bi-link',
            default => 'bi-file-earmark'
        };
    }
}
