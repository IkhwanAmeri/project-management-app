<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function index(Project $project, Request $request): ResourceCollection
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['assignedUser', 'creator'])
            ->search($request->input('search'))
            ->status($request->input('status'))
            ->priority($request->input('priority'))
            ->assignedTo($request->input('assignee'))
            ->latest()
            ->paginate();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('createTask', $project);

        $task = $this->taskService->create($project, $request->validated(), $request->user());

        $task->load(['assignedUser', 'creator']);

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        $task->load([
            'project',
            'assignedUser',
            'creator',
            'parentTask',
            'subtasks.assignedUser',
        ]);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        $task = $this->taskService->update($task, $request->validated(), $request->user());

        $task->load(['assignedUser', 'creator']);

        return new TaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task, request()->user());

        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
