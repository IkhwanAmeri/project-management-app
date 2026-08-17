<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskCommentRequest;
use App\Models\Task;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;

class TaskCommentController extends Controller
{
    /**
     * Inject the service responsible for comment logic and activity records.
     */
    public function __construct(private readonly CommentService $commentService)
    {
    }

    /**
     * Add a comment to a task.
     */
    public function store(StoreTaskCommentRequest $request, Task $task): RedirectResponse
    {
        $this->commentService->create($task, $request->user(), $request->string('comment')->toString());

        return to_route('tasks.show', $task)->with('status', 'Comment added successfully.');
    }
}
