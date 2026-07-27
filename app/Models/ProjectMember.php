<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'joined_at',
    ];

    /**
     * Convert the membership join time into a date-time object.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Get the project associated with this membership record.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user associated with this membership record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
