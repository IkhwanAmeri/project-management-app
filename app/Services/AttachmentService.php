<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    /**
     * Inject the service used to record attachment events.
     */
    public function __construct(private readonly ActivityService $activityService)
    {
    }

    /**
     * Store an uploaded file and save its metadata against a task.
     */
    public function create(Task $task, User $user, UploadedFile $file): TaskAttachment
    {
        $filePath = $file->store("tasks/{$task->id}", 'public');

        $attachment = $task->attachments()->create([
            'user_id' => $user->id,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $file->hashName(),
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'file_size' => (int) $file->getSize(),
        ]);

        $this->activityService->log(
            $user,
            'file_uploaded',
            $task->project,
            $task,
            'Uploaded file',
            [
                'attachment_id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'file_size' => $attachment->file_size,
            ],
        );

        return $attachment;
    }

    /**
     * Delete an attachment record and its stored file.
     */
    public function delete(TaskAttachment $attachment): void
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
    }
}
