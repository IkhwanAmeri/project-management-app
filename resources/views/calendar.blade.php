<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Calendar</h2>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12"
         x-data="{
             month: {{ now()->month - 1 }},
             year: {{ now()->year }},
             tasks: @js($tasks),
             get daysInMonth() { return new Date(this.year, this.month + 1, 0).getDate(); },
             get firstDayOfWeek() { return new Date(this.year, this.month, 1).getDay(); },
             get monthName() { return new Date(this.year, this.month).toLocaleString('default', { month: 'long' }); },
             prevMonth() { if (this.month === 0) { this.month = 11; this.year--; } else { this.month--; } },
             nextMonth() { if (this.month === 11) { this.month = 0; this.year++; } else { this.month++; } },
             tasksOnDay(day) {
                 const pad = n => String(n).padStart(2, '0');
                 const date = this.year + '-' + pad(this.month + 1) + '-' + pad(day);
                 return this.tasks.filter(t => {
                     const start = t.start_date;
                     const due = t.due_date;
                     if (start && due) return date >= start && date <= due;
                     if (start) return date === start;
                     if (due) return date === due;
                     return false;
                 });
             },
             statusColor(s) { return {'Todo':'bg-gray-400','In Progress':'bg-blue-500','Review':'bg-amber-500','Completed':'bg-emerald-500','Cancelled':'bg-red-400'}[s] || 'bg-gray-400'; },
             isToday(day) { return new Date(this.year, this.month, day).toDateString() === new Date().toDateString(); }
         }">

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            {{-- Month Navigation --}}
            <div class="rounded-2xl border border-white/40 bg-white/60 px-6 py-4 shadow-lg backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <button @click="prevMonth()" class="rounded-xl p-2.5 text-gray-400 transition hover:bg-white/60 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <h3 class="text-lg font-semibold text-gray-900" x-text="monthName + ' ' + year"></h3>
                    <button @click="nextMonth()" class="rounded-xl p-2.5 text-gray-400 transition hover:bg-white/60 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>

                {{-- Day Headers --}}
                <div class="mt-4 grid grid-cols-7 gap-px text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                    <div class="py-2">Sun</div>
                    <div class="py-2">Mon</div>
                    <div class="py-2">Tue</div>
                    <div class="py-2">Wed</div>
                    <div class="py-2">Thu</div>
                    <div class="py-2">Fri</div>
                    <div class="py-2">Sat</div>
                </div>

                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7 gap-px overflow-hidden rounded-xl bg-gray-100/60">
                    <template x-for="i in firstDayOfWeek" :key="'empty-'+i">
                        <div class="min-h-[6rem] bg-white/30"></div>
                    </template>

                    <template x-for="day in daysInMonth" :key="day">
                        <div class="min-h-[6rem] bg-white/50 p-2 transition hover:bg-white/80">
                            <p class="text-xs font-medium"
                               :class="isToday(day) ? 'flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white font-bold' : 'text-gray-500'"
                               x-text="day"></p>
                            <div class="mt-1.5 space-y-0.5">
                                <template x-for="task in tasksOnDay(day).slice(0, 4)" :key="task.id">
                                    <a :href="task.url" class="group/item flex items-center gap-1 rounded-md px-1.5 py-0.5 transition hover:opacity-80" :class="statusColor(task.status)">
                                        <span class="block truncate text-[10px] font-medium text-white" x-text="task.title"></span>
                                    </a>
                                </template>
                                <p x-show="tasksOnDay(day).length > 4" class="px-1.5 text-[9px] font-medium text-gray-400" x-text="'+' + (tasksOnDay(day).length - 4) + ' more'"></p>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Legend --}}
                <div class="mt-4 flex flex-wrap items-center gap-4 text-[10px] text-gray-500">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-gray-400"></span>Todo</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>In Progress</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>Review</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Completed</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>Cancelled</span>
                    <span class="ml-auto text-gray-400" x-text="tasks.length + ' total tasks'"></span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
