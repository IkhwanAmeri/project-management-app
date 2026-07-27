<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
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
     * Display a paginated list of projects.
     */
    public function index(): View
    {
        return view('projects.index', [
            'projects' => Project::query()
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
        $project->load(['owner', 'members', 'tasks.assignedUser']);

        return view('projects.show', compact('project'));
    }

    /**
     * Display the form used to edit an existing project.
     */
    public function edit(Project $project): View
    {
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
        $project->delete();

        return to_route('projects.index')->with('status', 'Project deleted successfully.');
    }
}
