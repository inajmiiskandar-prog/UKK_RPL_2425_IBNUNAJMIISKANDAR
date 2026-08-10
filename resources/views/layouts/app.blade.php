<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', config('app.name'))</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-50">
    {{-- TODO: Sidebar + Navbar (branding "Aplikasi Management System Pelanggan") --}}
    <main class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
        @yield('content')
    </main>
</body>
</html>
