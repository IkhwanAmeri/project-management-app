<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'parent_task_id',
        'title',
        'description',
        'priority',
        'status',
        'assigned_to',
        'created_by',
        'start_date',
        'due_date',
        'completed_at',
        'estimated_hours',
        'actual_hours',
    ];

    /**
     * Convert task date and numeric values to their appropriate types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
        ];
    }

    /**
     * Get the project that contains this task.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user assigned to this task.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get this task's optional parent task.
     */
    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_task_id');
    }

    /**
     * Get the subtasks directly under this task.
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_task_id');
    }

    /**
     * Get the comments posted on this task.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Get the files attached to this task.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    /**
     * Get the activity records associated with this task.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
