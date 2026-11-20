<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', config('app.name', 'Sistem Kuisioner'))</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>
@endif

<style>
    body {
        font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
    }

    .sidebar-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.35);
        border-radius: 9999px;
    }
</style>

@stack('styles')
