<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $project->name }} — Board</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $taskCount }} tasks</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('projects.show', $project) }}" class="rounded-xl border border-gray-200 bg-white/50 px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-white/80 hover:text-gray-900">Details</a>
                @can('createTask', $project)
                    <a href="{{ route('projects.tasks.create', $project) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-indigo-500 hover:shadow-lg">Create Task</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12"
         x-data="kanban()"
         x-cloak>

        {{-- Toast notifications --}}
        <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
            <template x-if="toast.show">
                <div class="rounded-xl px-4 py-3 text-sm font-medium shadow-lg backdrop-blur-sm transition-all duration-300"
                     :class="toast.type === 'success' ? 'bg-emerald-500/90 text-white' : 'bg-red-500/90 text-white'"
                     x-text="toast.message"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"></div>
            </template>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @php
                $columns = [
                    'Todo' => ['bg' => 'bg-gray-50', 'header' => 'bg-gray-100 text-gray-700', 'dot' => 'bg-gray-400'],
                    'In Progress' => ['bg' => 'bg-blue-50', 'header' => 'bg-blue-100 text-blue-700', 'dot' => 'bg-blue-400'],
                    'Review' => ['bg' => 'bg-amber-50', 'header' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-400'],
                    'Completed' => ['bg' => 'bg-emerald-50', 'header' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-400'],
                ];
                $priorityStyles = [
                    'Low' => 'bg-gray-100 text-gray-600',
                    'Medium' => 'bg-blue-100 text-blue-700',
                    'High' => 'bg-orange-100 text-orange-700',
                    'Critical' => 'bg-red-100 text-red-700',
                ];
            @endphp

            <div class="flex gap-6 overflow-x-auto pb-4">
                @foreach ($columns as $status => $style)
                    @php
                        $columnTasks = $tasks->get($status, collect());
                    @endphp
                    <div class="w-80 shrink-0">
                        {{-- Column Header --}}
                        <div class="rounded-2xl {{ $style['header'] }} px-4 py-3 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $style['dot'] }}"></span>
                                    <h3 class="text-sm font-semibold">{{ $status }}</h3>
                                </div>
                                <span class="rounded-full bg-white/60 px-2 py-0.5 text-xs font-bold"
                                      :id="'count-{{ str_replace(' ', '-', $status) }}'"
                                      x-text="counts['{{ $status }}']">{{ $columnTasks->count() }}</span>
                            </div>
                        </div>

                        {{-- Column Body --}}
                        <div class="mt-3 space-y-3 min-h-[120px] rounded-2xl border border-white/40 bg-white/30 p-3 backdrop-blur-sm transition-colors duration-200"
                             :id="'column-{{ str_replace(' ', '-', $status) }}'"
                             :class="dragOverColumn === '{{ $status }}' ? 'bg-indigo-100/60 border-indigo-300/60' : ''"
                             @dragover.prevent="dragOverColumn = '{{ $status }}'"
                             @dragenter.prevent="dragOverColumn = '{{ $status }}'"
                             @dragleave.prevent="if (dragOverColumn === '{{ $status }}') dragOverColumn = null"
                             @drop.prevent="handleDrop('{{ $status }}', $event)">

                            @forelse ($columnTasks as $task)
                                <div class="group relative rounded-xl border border-white/40 bg-white/70 p-4 shadow-sm backdrop-blur-sm transition hover:shadow-md hover:bg-white/90"
                                     id="task-card-{{ $task->id }}"
                                     draggable="true"
                                     @dragstart="handleDragStart({{ $task->id }}, '{{ $status }}', $event)"
                                     @dragend="handleDragEnd()"
                                     :class="draggingTaskId === {{ $task->id }} ? 'opacity-50 scale-95' : ''"
                                     :class="loadingTaskId === {{ $task->id }} ? 'pointer-events-none opacity-70' : ''">

                                    {{-- Loading overlay --}}
                                    <div x-show="loadingTaskId === {{ $task->id }}"
                                         x-transition
                                         class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/70 backdrop-blur-sm">
                                        <svg class="h-5 w-5 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    <a href="{{ route('tasks.show', $task) }}" class="block pointer-events-auto">
                                        {{-- Title --}}
                                        <h4 class="text-sm font-semibold text-gray-900 line-clamp-2">{{ $task->title }}</h4>

                                        {{-- Priority & Due Date --}}
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                            <span class="inline-flex rounded-full px-2 py-0.5 font-semibold {{ $priorityStyles[$task->priority] ?? 'bg-gray-100 text-gray-600' }}">{{ $task->priority }}</span>
                                            @if ($task->due_date)
                                                <span class="text-gray-500">Due {{ $task->due_date->format('M j') }}</span>
                                            @endif
                                        </div>

                                        {{-- Assignee --}}
                                        @if ($task->assignedUser)
                                            <div class="mt-2 flex items-center gap-1.5">
                                                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-500 text-[8px] font-bold text-white">{{ strtoupper(substr($task->assignedUser->name, 0, 1)) }}</div>
                                                <span class="text-xs text-gray-600">{{ $task->assignedUser->name }}</span>
                                            </div>
                                        @endif

                                        {{-- Counts --}}
                                        <div class="mt-3 flex items-center gap-3 text-[11px] text-gray-400">
                                            {{-- Comments --}}
                                            <span class="flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                                                {{ $task->comments_count }}
                                            </span>
                                            {{-- Attachments --}}
                                            <span class="flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.23m.001-.001l-.01.01m-.01-.01l-.01-.01" /></svg>
                                                {{ $task->attachments_count }}
                                            </span>
                                            {{-- Subtasks --}}
                                            @if ($task->subtasks->count())
                                                <span class="flex items-center gap-1">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                                                    {{ $task->completedSubtasksCount() }}/{{ $task->subtasks->count() }}
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center" id="empty-{{ str_replace(' ', '-', $status) }}">
                                    <p class="text-xs text-gray-400">No tasks</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        function kanban() {
            return {
                draggingTaskId: null,
                dragFromStatus: null,
                dragOverColumn: null,
                loadingTaskId: null,
                canDrag: {{ $canDrag ? 'true' : 'false' }},
                toast: { show: false, message: '', type: 'success' },
                counts: {
                    'Todo': {{ $tasks->get('Todo', collect())->count() }},
                    'In Progress': {{ $tasks->get('In Progress', collect())->count() }},
                    'Review': {{ $tasks->get('Review', collect())->count() }},
                    'Completed': {{ $tasks->get('Completed', collect())->count() }},
                },

                handleDragStart(taskId, status, event) {
                    if (!this.canDrag) {
                        event.preventDefault();
                        this.showToast('Only Owners and Managers can move tasks on this board.', 'error');
                        return;
                    }

                    this.draggingTaskId = taskId;
                    this.dragFromStatus = status;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', taskId);
                },

                handleDragEnd() {
                    this.draggingTaskId = null;
                    this.dragFromStatus = null;
                    this.dragOverColumn = null;
                },

                async handleDrop(newStatus, event) {
                    if (!this.canDrag) {
                        this.showToast('Only Owners and Managers can move tasks on this board.', 'error');
                        this.handleDragEnd();
                        return;
                    }

                    const taskId = this.draggingTaskId;
                    const fromStatus = this.dragFromStatus;

                    if (!taskId || !fromStatus || fromStatus === newStatus) {
                        this.handleDragEnd();
                        return;
                    }

                    this.dragOverColumn = null;
                    this.loadingTaskId = taskId;

                    const card = document.getElementById('task-card-' + taskId);

                    try {
                        const url = '{{ route('projects.tasks.status', ['project' => $project, 'task' => 'TASK_ID']) }}'.replace('TASK_ID', taskId);
                        const response = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ status: newStatus }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || data.errors?.status?.[0] || 'Failed to update status.');
                        }

                        if (card) {
                            const emptyMsg = document.getElementById('empty-' + this.statusToId(newStatus));
                            if (emptyMsg) emptyMsg.remove();

                            const targetColumn = document.getElementById('column-' + this.statusToId(newStatus));
                            if (targetColumn) targetColumn.appendChild(card);
                        }

                        this.counts[fromStatus]--;
                        this.counts[newStatus]++;

                        this.showToast('Task moved successfully.', 'success');
                    } catch (error) {
                        if (card) {
                            const sourceColumn = document.getElementById('column-' + this.statusToId(fromStatus));
                            if (sourceColumn) sourceColumn.appendChild(card);
                        }

                        this.showToast(error.message, 'error');
                    } finally {
                        this.loadingTaskId = null;
                        this.handleDragEnd();
                    }
                },

                statusToId(status) {
                    return status.replace(/\s+/g, '-');
                },

                showToast(message, type) {
                    this.toast = { show: true, message, type };
                    setTimeout(() => { this.toast.show = false; }, 3000);
                },
            };
        }
    </script>
</x-app-layout>
