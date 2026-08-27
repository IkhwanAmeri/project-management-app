<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Inject the service responsible for project business logic.
     */
    public function __construct(private readonly ProjectService $projectService)
    {
    }

    /**
     * Display a paginated list of projects the authenticated user belongs to.
     */
    public function index(Request $request): View
    {
        return view('projects.index', [
            'projects' => $request->user()->projects()
                ->with('owner')
                ->withCount('members')
                ->latest()
                ->paginate(),
        ]);
    }

    /**
     * Display the form used to create a project.
     */
    public function create(): View
    {
        return view('projects.create', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Validate and create a new project, then redirect to its details page.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $this->projectService->create($request->validated(), $request->user());

        return to_route('projects.show', $project)->with('status', 'Project created successfully.');
    }

    /**
     * Display one project with its owner and members.
     */
    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['owner', 'members', 'tasks.assignedUser']);

        return view('projects.show', compact('project'));
    }

    /**
     * Display the Kanban board for a project.
     */
    public function kanban(Project $project): View
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['assignedUser', 'subtasks'])
            ->withCount(['comments', 'attachments'])
            ->get()
            ->groupBy('status');

        return view('projects.kanban', compact('project', 'tasks'));
    }

    /**
     * Display the form used to edit an existing project.
     */
    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', [
            'project' => $project->load('members'),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Validate and save changes to a project.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->update($project, $request->validated());

        return to_route('projects.show', $project)->with('status', 'Project updated successfully.');
    }

    /**
     * Soft-delete a project, then return to the project list.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return to_route('projects.index')->with('status', 'Project deleted successfully.');
    }
}
