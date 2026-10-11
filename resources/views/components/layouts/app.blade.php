<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Poliklinik' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        crossorigin="anonymous">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-base-200">
    <header class="navbar bg-base-100 shadow-sm px-6">
        <a href="/" class="text-lg font-bold text-[#1e2d6b]">Poliklinik</a>
        @auth
            <div class="ml-auto flex items-center gap-4">
                <span>{{ Auth::user()->nama }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                </form>
            </div>
        @endauth
    </header>

    <main class="mx-auto max-w-5xl p-6">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>

</html>
