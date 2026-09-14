<nav x-data="{ open: false }" class="border-b border-gray-100 bg-white shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex">
                @php
                    $navLinks = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'url' => route('dashboard')],
                        ['route' => 'projects.*', 'label' => 'Projects', 'url' => route('projects.index')],
                        ['route' => 'tasks.*', 'label' => 'Tasks', 'url' => route('tasks.index')],
                        ['route' => 'calendar', 'label' => 'Calendar', 'url' => route('calendar')],
                        ['route' => 'activities.*', 'label' => 'Activity', 'url' => route('activities.index')],
                        ['route' => 'reports.*', 'label' => 'Reports', 'url' => route('reports.index')],
                    ];
                @endphp
                    @foreach ($navLinks as $link)
                        @php $isActive = request()->routeIs($link['route']); @endphp
                        <a href="{{ $link['url'] }}"
                           class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition
                                  {{ $isActive
                                      ? 'bg-indigo-50 text-indigo-700'
                                      : 'text-gray-500 hover:bg-white/60 hover:text-gray-700' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Right Side -->
            <div class="hidden sm:flex sm:items-center sm:gap-2">
                <!-- Notifications -->
                <div class="relative">
                    <x-dropdown align="right" width="w-[520px]">
                        <x-slot name="trigger">
                            <button class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl text-gray-400 transition hover:bg-white/60 hover:text-gray-600">
                                <span class="sr-only">Notifications</span>
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                @if (Auth::user()->unreadNotifications->count() > 0)
                                    <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white shadow-sm">
                                        {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-5 py-3 border-b border-gray-100">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Notifications</p>
                            </div>
                            @forelse (Auth::user()->unreadNotifications->take(5) as $notification)
                                <div class="border-b border-gray-50 px-5 py-4 last:border-0">
                                    <p class="text-sm leading-relaxed text-gray-700">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                    <div class="mt-2.5 flex items-center justify-between">
                                        <p class="text-[11px] text-gray-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg px-2.5 py-1 text-[11px] font-medium text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600">Dismiss</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-8 text-center">
                                    <svg class="mx-auto mb-2 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                                    <p class="text-xs text-gray-400">All caught up!</p>
                                </div>
                            @endforelse
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- User Menu -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-xl border border-white/40 bg-white/50 px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-white/80 hover:shadow-sm">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-500 text-[10px] font-bold text-white">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl p-2 text-gray-400 transition hover:bg-white/60 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="space-y-1 px-4 pt-2 pb-3">
            <a href="{{ route('dashboard') }}"
               class="block rounded-xl px-3 py-2 text-base font-medium transition
                      {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-white/60 hover:text-gray-700' }}">
                Dashboard
            </a>
            <a href="{{ route('projects.index') }}"
               class="block rounded-xl px-3 py-2 text-base font-medium transition
                      {{ request()->routeIs('projects.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-white/60 hover:text-gray-700' }}">
                Projects
            </a>
            <a href="{{ route('tasks.index') }}"
               class="block rounded-xl px-3 py-2 text-base font-medium transition
                      {{ request()->routeIs('tasks.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-white/60 hover:text-gray-700' }}">
                Tasks
            </a>
            <a href="{{ route('calendar') }}"
               class="block rounded-xl px-3 py-2 text-base font-medium transition
                      {{ request()->routeIs('calendar') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-white/60 hover:text-gray-700' }}">
                Calendar
            </a>
            <a href="{{ route('activities.index') }}"
               class="block rounded-xl px-3 py-2 text-base font-medium transition
                      {{ request()->routeIs('activities.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-white/60 hover:text-gray-700' }}">
                Activity
            </a>
            <a href="{{ route('reports.index') }}"
               class="block rounded-xl px-3 py-2 text-base font-medium transition
                      {{ request()->routeIs('reports.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-white/60 hover:text-gray-700' }}">
                Reports
            </a>
        </div>

        <!-- Responsive User Info -->
        <div class="border-t border-white/20 px-4 pt-4 pb-2">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-500 text-sm font-bold text-white">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-400">{{ Auth::user()->email }}</div>
                </div>
            </div>
        </div>

        <div class="space-y-1 px-4 pt-2 pb-4">
            {{-- Mobile Notifications --}}
            <div class="rounded-xl bg-white/40 px-3 py-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Notifications</span>
                    @if (Auth::user()->unreadNotifications->count() > 0)
                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                            {{ Auth::user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </div>
                @forelse (Auth::user()->unreadNotifications->take(3) as $notification)
                    <div class="mt-2 rounded-lg bg-white/60 px-3 py-2">
                        <p class="text-xs leading-relaxed text-gray-600">{{ $notification->data['message'] ?? 'New notification' }}</p>
                        <div class="mt-1.5 flex items-center justify-between">
                            <span class="text-[10px] text-gray-400">{{ $notification->created_at?->diffForHumans() }}</span>
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-[10px] font-medium text-indigo-500">Dismiss</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="mt-2 text-xs text-gray-400">All caught up!</p>
                @endforelse
            </div>

            <a href="{{ route('profile.edit') }}"
               class="block rounded-xl px-3 py-2 text-base font-medium text-gray-500 transition hover:bg-white/60 hover:text-gray-700">
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full rounded-xl px-3 py-2 text-left text-base font-medium text-gray-500 transition hover:bg-white/60 hover:text-gray-700">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</nav>
