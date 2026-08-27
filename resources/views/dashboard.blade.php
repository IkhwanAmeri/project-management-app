<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">

            {{-- Stat Cards --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $statStyles = [
                        ['color' => 'indigo', 'bg' => 'bg-indigo-500/10', 'border' => 'border-l-indigo-500', 'text' => 'text-indigo-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />'],
                        ['color' => 'blue', 'bg' => 'bg-blue-500/10', 'border' => 'border-l-blue-500', 'text' => 'text-blue-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                        ['color' => 'emerald', 'bg' => 'bg-emerald-500/10', 'border' => 'border-l-emerald-500', 'text' => 'text-emerald-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                        ['color' => 'rose', 'bg' => 'bg-rose-500/10', 'border' => 'border-l-rose-500', 'text' => 'text-rose-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />'],
                    ];
                @endphp

                @foreach ($statistics as $i => [$label, $value])
                    @php $style = $statStyles[$i]; @endphp
                    <div class="rounded-2xl border border-white/40 border-l-4 {{ $style['border'] }} bg-white/60 p-6 shadow-lg backdrop-blur-sm transition hover:shadow-xl hover:bg-white/80">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ $value }}</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $style['bg'] }}">
                                <svg class="h-6 w-6 {{ $style['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $style['icon'] !!}
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tasks & Comments --}}
            <div class="grid gap-6 lg:grid-cols-2">

                {{-- My Tasks --}}
                <div class="rounded-2xl border border-white/40 bg-white/60 shadow-lg backdrop-blur-sm">
                    <div class="border-b border-white/40 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">My Tasks</h3>
                    </div>
                    <ul class="divide-y divide-gray-100/60">
                        @forelse ($tasks as $task)
                            <li class="group px-6 py-4 transition hover:bg-white/40">
                                <a href="{{ route('tasks.show', $task) }}" class="block">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 transition">{{ $task->title }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $task->project->name }}</p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-2">
                                            @php
                                                $statusStyles = [
                                                    'Todo' => 'bg-gray-100 text-gray-600',
                                                    'In Progress' => 'bg-blue-100 text-blue-700',
                                                    'Review' => 'bg-amber-100 text-amber-700',
                                                    'Completed' => 'bg-emerald-100 text-emerald-700',
                                                    'Cancelled' => 'bg-red-100 text-red-600',
                                                ];
                                                $priorityColors = [
                                                    'Low' => 'bg-gray-400',
                                                    'Medium' => 'bg-blue-400',
                                                    'High' => 'bg-orange-400',
                                                    'Critical' => 'bg-red-500',
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusStyles[$task->status] ?? 'bg-gray-100 text-gray-600' }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $priorityColors[$task->priority] ?? 'bg-gray-400' }}"></span>
                                                {{ $task->status }}
                                            </span>
                                            @if ($task->due_date)
                                                <span @class([
                                                    'whitespace-nowrap text-xs font-medium',
                                                    'text-red-600' => $task->due_date->isPast() && ! $task->due_date->isToday(),
                                                    'text-amber-600' => $task->due_date->isToday(),
                                                    'text-gray-500' => $task->due_date->isFuture(),
                                                ])>
                                                    {{ $task->due_date->isToday() ? 'Today' : ($task->due_date->isTomorrow() ? 'Tomorrow' : $task->due_date->format('M j')) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="px-6 py-8 text-center text-sm text-gray-500">
                                <svg class="mx-auto mb-2 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                </svg>
                                No open tasks assigned to you.
                            </li>
                        @endforelse
                    </ul>
                    @if ($tasks->count())
                        <div class="border-t border-white/40 px-6 py-3 text-center">
                            <a href="{{ route('tasks.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 transition">View all tasks &rarr;</a>
                        </div>
                    @endif
                </div>

                {{-- Recent Comments --}}
                <div class="rounded-2xl border border-white/40 bg-white/60 shadow-lg backdrop-blur-sm">
                    <div class="border-b border-white/40 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Recent Comments</h3>
                    </div>
                    <ul class="divide-y divide-gray-100/60">
                        @forelse ($recentComments as $comment)
                            <li class="group px-6 py-4 transition hover:bg-white/40">
                                <div class="flex items-start gap-3">
                                    @php
                                        $avatarColors = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-violet-500'];
                                        $colorIndex = crc32($comment->user->name) % count($avatarColors);
                                    @endphp
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $avatarColors[$colorIndex] }} text-xs font-bold text-white">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                            <p class="shrink-0 text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                                        </div>
                                        <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $comment->comment }}</p>
                                        <a href="{{ route('tasks.show', $comment->task) }}" class="mt-1.5 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-500 transition">
                                            <span class="truncate">{{ $comment->task->project->name }}</span>
                                            <span class="text-gray-300">&middot;</span>
                                            <span class="truncate">{{ $comment->task->title }}</span>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="px-6 py-8 text-center text-sm text-gray-500">
                                <svg class="mx-auto mb-2 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                </svg>
                                No comments from other project members yet.
                            </li>
                        @endforelse
                    </ul>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
