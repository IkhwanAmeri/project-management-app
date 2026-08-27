<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">Create project</h2></x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('projects.store') }}" class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                @csrf
                @include('projects._form')
                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('projects.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-white/60">Cancel</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-indigo-500 hover:shadow-lg">Submit</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
