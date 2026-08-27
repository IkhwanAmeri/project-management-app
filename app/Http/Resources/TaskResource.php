<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'created_at' => $this->created_at?->toISOString(),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'parent_task' => new self($this->whenLoaded('parentTask')),
            'subtasks' => self::collection($this->whenLoaded('subtasks')),
        ];
    }
}
