<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'start_date',
        'end_date',
    ];

    /**
     * Convert project date attributes into date objects.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the user who created and owns this project.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all users assigned to this project, including membership details.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'joined_at')
            ->withCasts(['joined_at' => 'datetime'])
            ->withTimestamps();
    }

    /**
     * Get all tasks belonging to this project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
