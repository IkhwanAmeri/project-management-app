<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'task_id',
        'user_id',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    /**
     * Cast file sizes to integers.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /**
     * Get the task this attachment belongs to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who uploaded this attachment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
