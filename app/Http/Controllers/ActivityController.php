<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Display a paginated timeline of activities across the user's projects.
     */
    public function index(Request $request): View
    {
        $projectIds = $request->user()->projects()->pluck('projects.id');

        $activities = Activity::query()
            ->with(['user', 'project', 'task'])
            ->whereIn('project_id', $projectIds)
            ->latest()
            ->paginate(20);

        return view('activities.index', compact('activities'));
    }

    /**
     * Display a single activity with its related models.
     */
    public function show(Request $request, Activity $activity): View
    {
        $isMember = $request->user()->projects()
            ->where('projects.id', $activity->project_id)
            ->exists();

        if (! $isMember) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $activity->load(['user', 'project', 'task']);

        return view('activities.show', compact('activity'));
    }
}
