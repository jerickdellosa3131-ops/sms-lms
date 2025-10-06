<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'message',
        'posted_by',
        'posted_at',
        'expires_at',
        'is_pinned'
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    /**
     * Get the class that owns the announcement
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the poster (teacher/admin)
     */
    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Check if announcement is expired
     */
    public function isExpired()
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    /**
     * Check if announcement is active
     */
    public function isActive()
    {
        return !$this->isExpired();
    }

    /**
     * Scope active announcements
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope pinned announcements
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}
