<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;

class CommentService
{
    /**
     * Inject the service used to record comment events.
     */
    public function __construct(private readonly ActivityService $activityService)
    {
    }

    /**
     * Add a comment to a task on behalf of a user.
     */
    public function create(Task $task, User $user, string $comment): TaskComment
    {
        $taskComment = $task->comments()->create([
            'user_id' => $user->id,
            'comment' => $comment,
        ]);

        $this->activityService->log(
            $user,
            'comment_added',
            $task->project,
            $task,
            'Added comment',
            ['comment_id' => $taskComment->id],
        );

        return $taskComment;
    }

    /**
     * Update a comment and record when it was edited.
     */
    public function update(TaskComment $comment, string $content): TaskComment
    {
        $comment->update([
            'comment' => $content,
            'edited_at' => now(),
        ]);

        return $comment->refresh();
    }

    /**
     * Soft-delete a comment.
     */
    public function delete(TaskComment $comment): void
    {
        $comment->delete();
    }
}
