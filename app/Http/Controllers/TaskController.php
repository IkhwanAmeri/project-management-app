<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Inject the service responsible for task business logic.
     */
    public function __construct(private readonly TaskService $taskService)
    {
    }

    /**
     * Display the user's project tasks with search, filters, and sorting.
     */
    public function index(Request $request): View
    {
        $sortOptions = [
            'newest' => ['column' => 'created_at', 'direction' => 'desc'],
            'oldest' => ['column' => 'created_at', 'direction' => 'asc'],
            'due_date' => ['column' => 'due_date', 'direction' => 'asc'],
            'priority' => ['column' => 'priority', 'direction' => 'asc'],
            'recently_updated' => ['column' => 'updated_at', 'direction' => 'desc'],
        ];

        $sortKey = array_key_exists($request->input('sort'), $sortOptions) ? $request->input('sort') : 'due_date';
        $sort = $sortOptions[$sortKey];

        $projectIds = $request->user()->projects()->pluck('projects.id');

        $tasks = Task::query()
            ->with(['project', 'assignedUser'])
            ->whereIn('project_id', $projectIds)
            ->search($request->input('search'))
            ->status($request->input('status'))
            ->priority($request->input('priority'))
            ->assignedTo($request->input('assignee'))
            ->when($request->filled('due_date_from'), fn ($q) => $q->where('due_date', '>=', $request->input('due_date_from')))
            ->when($request->filled('due_date_to'), fn ($q) => $q->where('due_date', '<=', $request->input('due_date_to')))
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy($sort['column'], $sort['direction'])
            ->paginate()
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'assignees' => $request->user()->projects()
                ->with('members:id,name')
                ->get()
                ->pluck('members')
                ->flatten()
                ->unique('id')
                ->sortBy('name'),
            'sortOptions' => $sortOptions,
            'currentSort' => $sortKey,
        ]);
    }

    /**
     * Display a task creation form with the project's available members.
     */
    public function create(Project $project): View
    {
        $this->authorize('createTask', $project);

        return view('tasks.create', [
            'project' => $project,
            'members' => $project->members()->orderBy('name')->get(),
            'parentTasks' => $project->tasks()->orderBy('title')->get(),
        ]);
    }

    /**
     * Create a task under the selected project.
     */
    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $task = $this->taskService->create($project, $request->validated(), $request->user());

        return to_route('tasks.show', $task)->with('status', 'Task created successfully.');
    }

    /**
     * Display a task creation form for a subtask under the current task.
     */
    public function createSubtask(Task $task): View
    {
        $project = $task->project;

        $this->authorize('createTask', $project);

        return view('tasks.create', [
            'project' => $project,
            'parentTask' => $task,
            'members' => $project->members()->orderBy('name')->get(),
            'parentTasks' => collect(),
        ]);
    }

    /**
     * Create a subtask and attach it to its parent task.
     */
    public function storeSubtask(StoreTaskRequest $request, Task $task): RedirectResponse
    {
        $subtask = $this->taskService->createSubtask($task, $request->validated(), $request->user());

        return to_route('tasks.show', $subtask)->with('status', 'Subtask created successfully.');
    }

    /**
     * Display a task with its project and user details.
     */
    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load([
            'project',
            'project.members',
            'assignedUser',
            'creator',
            'parentTask',
            'subtasks.assignedUser',
            'comments.user',
            'attachments.user',
        ]);

        return view('tasks.show', compact('task'));
    }

    /**
     * Display the form used to update a task.
     */
    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', [
            'task' => $task,
            'project' => $task->project,
            'members' => $task->project->members()->orderBy('name')->get(),
            'parentTasks' => $task->project->tasks()->whereKeyNot($task->id)->orderBy('title')->get(),
        ]);
    }

    /**
     * Save task changes, including a completed timestamp when applicable.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->taskService->update($task, $request->validated(), $request->user());

        return to_route('tasks.show', $task)->with('status', 'Task updated successfully.');
    }

    /**
     * Soft-delete a task and return to its project details.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $project = $task->project;
        $this->taskService->delete($task, request()->user());

        return to_route('projects.show', $project)->with('status', 'Task deleted successfully.');
    }

    /**
     * Mark a task as completed and set its completion time.
     */
    public function complete(Task $task): RedirectResponse
    {
        $this->authorize('complete', $task);

        $this->taskService->complete($task, request()->user());

        return back()->with('status', 'Task marked as completed.');
    }

    /**
     * Create a duplicate of the given task.
     */
    public function duplicate(Task $task): RedirectResponse
    {
        $this->authorize('createTask', $task->project);

        $duplicate = $this->taskService->duplicate($task, request()->user());

        return to_route('tasks.show', $duplicate)->with('status', 'Task duplicated successfully.');
    }

    /**
     * Change the status of a task via inline quick action.
     */
    public function changeStatus(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('changeStatus', $task);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled'])],
        ]);

        $this->taskService->update($task, $validated, $request->user());

        return back()->with('status', 'Status updated successfully.');
    }

    /**
     * Change the priority of a task via inline quick action.
     */
    public function changePriority(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('changePriority', $task);

        $validated = $request->validate([
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High', 'Critical'])],
        ]);

        $this->taskService->update($task, $validated, $request->user());

        return back()->with('status', 'Priority updated successfully.');
    }

    /**
     * Assign a task to a project member via inline quick action.
     */
    public function assign(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('assign', $task);

        $projectId = $task->project_id;

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'integer', Rule::exists('project_members', 'user_id')->where('project_id', $projectId)],
        ]);

        $this->taskService->update($task, $validated, $request->user());

        return back()->with('status', 'Assignment updated successfully.');
    }

    /**
     * Update a task's status from the Kanban board drag-and-drop.
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Project $project, Task $task): JsonResponse
    {
        $task = $this->taskService->update($task, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Task status updated successfully.',
            'task' => [
                'id' => $task->id,
                'status' => $task->status,
                'completed_at' => $task->completed_at?->toISOString(),
            ],
        ]);
    }
}
