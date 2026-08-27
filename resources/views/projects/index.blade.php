<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Projects</h2>
            <a href="{{ route('projects.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-indigo-500 hover:shadow-lg">New project</a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm font-medium text-emerald-700 shadow-sm backdrop-blur-sm">{{ session('status') }}</div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $statusStyles = [
                        'planning' => 'bg-amber-100 text-amber-700',
                        'active' => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-gray-100 text-gray-500',
                    ];
                @endphp

                @forelse ($projects as $project)
                    @php $isOwner = $project->created_by === Auth::id(); @endphp
                    <a href="{{ route('projects.show', $project) }}" class="group rounded-2xl border {{ $isOwner ? 'border-indigo-200 bg-indigo-50/40' : 'border-white/40 bg-white/60' }} p-6 shadow-lg backdrop-blur-sm transition hover:shadow-xl hover:bg-white/80">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-indigo-600 transition">{{ $project->name }}</h3>
                                    @if ($isOwner)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-600">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>
                                            My Project
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-semibold capitalize {{ $statusStyles[$project->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ $project->status }}
                            </span>
                        </div>

                        @if ($project->description)
                            <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ $project->description }}</p>
                        @endif

                        <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                {{ $project->owner->name }}
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                                {{ $project->members_count }} member{{ $project->members_count !== 1 ? 's' : '' }}
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                                {{ $project->start_date?->format('M j') ?? '—' }} &ndash; {{ $project->end_date?->format('M j, Y') ?? '—' }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-white/40 bg-white/60 px-6 py-12 text-center shadow-lg backdrop-blur-sm">
                        <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                        <p class="text-sm text-gray-500">No projects yet. Create your first project to get started.</p>
                        <a href="{{ route('projects.create') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-500">New project &rarr;</a>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $projects->links() }}</div>
        </div>
    </div>
</x-app-layout>
