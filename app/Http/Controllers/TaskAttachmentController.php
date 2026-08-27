<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskAttachmentRequest;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\AttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskAttachmentController extends Controller
{
    /**
     * Inject the service responsible for attachment storage and activity records.
     */
    public function __construct(private readonly AttachmentService $attachmentService)
    {
    }

    /**
     * Upload and attach a file to a task.
     */
    public function store(StoreTaskAttachmentRequest $request, Task $task): RedirectResponse
    {
        $this->attachmentService->create($task, $request->user(), $request->file('attachment'));

        return to_route('tasks.show', $task)->with('status', 'File uploaded successfully.');
    }

    /**
     * Download an attachment using its original filename.
     */
    public function download(TaskAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment->task);

        abort_unless(Storage::disk('public')->exists($attachment->file_path), 404);

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }
}
