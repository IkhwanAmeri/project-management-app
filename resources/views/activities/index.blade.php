<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Activity Log</h2>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            @php
                $actionConfig = [
                    'project_created'       => ['icon' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25', 'color' => 'bg-indigo-100 text-indigo-600'],
                    'task_created'          => ['icon' => 'M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-blue-100 text-blue-600'],
                    'subtask_created'       => ['icon' => 'M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-cyan-100 text-cyan-600'],
                    'task_status_changed'   => ['icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99', 'color' => 'bg-amber-100 text-amber-600'],
                    'task_priority_changed' => ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'color' => 'bg-orange-100 text-orange-600'],
                    'task_assignee_changed' => ['icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z', 'color' => 'bg-violet-100 text-violet-600'],
                    'task_duplicated'       => ['icon' => 'M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75', 'color' => 'bg-teal-100 text-teal-600'],
                    'task_deleted'          => ['icon' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0', 'color' => 'bg-red-100 text-red-600'],
                    'comment_added'         => ['icon' => 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z', 'color' => 'bg-emerald-100 text-emerald-600'],
                    'file_uploaded'         => ['icon' => 'M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.23m.001-.001l-.01.01m-.01-.01l-.01-.01', 'color' => 'bg-pink-100 text-pink-600'],
                ];
            @endphp

            @if ($activities->isEmpty())
                <div class="rounded-2xl border border-white/40 bg-white/60 p-12 text-center shadow-lg backdrop-blur-sm">
                    <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-sm text-gray-500">No activity recorded yet.</p>
                </div>
            @else
                <div class="relative">
                    {{-- Timeline line --}}
                    <div class="absolute left-5 top-0 h-full w-px bg-gray-200/80"></div>

                    <div class="space-y-1">
                        @foreach ($activities as $activity)
                            @php
                                $config = $actionConfig[$activity->action] ?? ['icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'bg-gray-100 text-gray-600'];
                            @endphp
                            <a href="{{ route('activities.show', $activity) }}" class="group relative flex gap-4 rounded-2xl border border-white/40 bg-white/60 p-4 shadow-sm backdrop-blur-sm transition hover:shadow-md hover:bg-white/90">
                                {{-- Icon --}}
                                <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $config['color'] }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $config['icon'] }}" /></svg>
                                </div>

                                {{-- Content --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $activity->user->name }}</span>
                                        <span class="text-sm text-gray-500">{{ $activity->description }}</span>
                                    </div>

                                    @if ($activity->task)
                                        <p class="mt-0.5 text-xs text-gray-400">Task: {{ $activity->task->title ?? 'Deleted task' }}</p>
                                    @endif

                                    @if ($activity->properties)
                                        @if ($activity->action === 'task_status_changed' && isset($activity->properties['from'], $activity->properties['to']))
                                            <div class="mt-1.5 flex items-center gap-1.5">
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">{{ $activity->properties['from'] }}</span>
                                                <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">{{ $activity->properties['to'] }}</span>
                                            </div>
                                        @elseif ($activity->action === 'task_priority_changed' && isset($activity->properties['from'], $activity->properties['to']))
                                            <div class="mt-1.5 flex items-center gap-1.5">
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">{{ $activity->properties['from'] }}</span>
                                                <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                                                <span class="rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-semibold text-orange-700">{{ $activity->properties['to'] }}</span>
                                            </div>
                                        @endif
                                    @endif

                                    <div class="mt-1.5 flex items-center gap-2 text-[11px] text-gray-400">
                                        @if ($activity->project)
                                            <span>{{ $activity->project->name }}</span>
                                            <span>&middot;</span>
                                        @endif
                                        <span>{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-8">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
