<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Project Management') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans text-gray-900">
        <main class="mx-auto flex min-h-screen max-w-4xl items-center justify-center p-6">
            <section class="w-full rounded-xl bg-white p-10 text-center shadow-sm">
                <h1 class="text-3xl font-semibold">Project Management System</h1>
                <p class="mt-3 text-gray-600">Organize projects, collaborate with members, and track tasks in one place.</p>
                <div class="mt-8 flex justify-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Open Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Register</a>
                    @endauth
                </div>
            </section>
        </main>
    </body>
</html>
