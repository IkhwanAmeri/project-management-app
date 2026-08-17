<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Display the user's project tasks with search, filters, and due-date sorting.
     */
    public function index(Request $request): View
    {
        $projectIds = $request->user()->projects()->pluck('projects.id');
        $tasks = Task::query()
            ->with(['project', 'assignedUser'])
            ->whereIn('project_id', $projectIds)
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->input('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->input('priority')))
            ->when($request->filled('assignee'), fn ($query) => $query->where('assigned_to', $request->integer('assignee')))
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date', $request->input('sort') === 'due_desc' ? 'desc' : 'asc')
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
        ]);
    }

    /**
     * Display a task creation form with the project's available members.
     */
    public function create(Project $project): View
    {
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
        $task->load([
            'project',
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
        $project = $task->project;
        $this->taskService->delete($task, request()->user());

        return to_route('projects.show', $project)->with('status', 'Task deleted successfully.');
    }

    /**
     * Mark a task as completed and set its completion time.
     */
    public function complete(Task $task): RedirectResponse
    {
        $this->taskService->complete($task, request()->user());

        return back()->with('status', 'Task marked as completed.');
    }
}
