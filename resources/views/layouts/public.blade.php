<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $globalSetting->site_name ?? 'MUA Portfolio')</title>
    <meta name="application-name" content="{{ $globalSetting->site_name ?? 'MUA Portfolio' }}">
    <meta name="description" content="@yield('meta_description', $globalSetting->meta_description ?? '')">
    @if(!empty($globalSetting?->favicon))
        <link rel="icon" href="{{ asset('storage/' . $globalSetting->favicon) }}">
    @endif
    <style>
        :root {
            --theme-primary: #0f2747;
            --theme-secondary: #dbe8f7;
        }

        body.site-bg {
            background-color: #f8fafc !important;
            background-image: none !important;
        }

    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-bg text-slate-800 min-h-screen flex flex-col">
<header class="public-header sticky top-0 z-50 backdrop-blur-md">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="public-brand flex items-center gap-3 font-serif text-2xl tracking-wide">
            @if(!empty($globalSetting?->logo))
                <img src="{{ asset('storage/' . $globalSetting->logo) }}" alt="Logo" class="h-10 w-10 rounded-xl object-cover">
            @endif
            <span>{{ $globalSetting->site_name ?? 'Aurora Beauty MUA' }}</span>
        </a>

        <button class="md:hidden btn-secondary" type="button" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')">Menu</button>

        <nav class="public-nav hidden md:flex items-center gap-6 text-sm font-medium">
            <a class="{{ request()->routeIs('home') ? 'chip-active' : '' }}" href="{{ route('home') }}">Beranda</a>
            <a class="{{ request()->routeIs('portfolio.*') ? 'chip-active' : '' }}" href="{{ route('portfolio.index') }}">Portfolio</a>
            <a class="{{ request()->routeIs('pricelist') ? 'chip-active' : '' }}" href="{{ route('pricelist') }}">Daftar Harga</a>
            <a class="{{ request()->routeIs('faq') ? 'chip-active' : '' }}" href="{{ route('faq') }}">FAQ</a>
            <a class="{{ request()->routeIs('contact') ? 'chip-active' : '' }}" href="{{ route('contact') }}">Kontak</a>
            <a href="{{ route('booking.create') }}" class="btn-primary">Reservasi</a>
        </nav>
    </div>

    <nav id="mobile-nav" class="public-mobile-nav md:hidden hidden px-4 py-4">
        <div class="grid gap-3 text-sm font-medium">
            <a class="{{ request()->routeIs('home') ? 'chip-active' : '' }}" href="{{ route('home') }}">Beranda</a>
            <a class="{{ request()->routeIs('portfolio.*') ? 'chip-active' : '' }}" href="{{ route('portfolio.index') }}">Portfolio</a>
            <a class="{{ request()->routeIs('pricelist') ? 'chip-active' : '' }}" href="{{ route('pricelist') }}">Daftar Harga</a>
            <a class="{{ request()->routeIs('faq') ? 'chip-active' : '' }}" href="{{ route('faq') }}">FAQ</a>
            <a class="{{ request()->routeIs('contact') ? 'chip-active' : '' }}" href="{{ route('contact') }}">Kontak</a>
            <a href="{{ route('booking.create') }}" class="btn-primary text-center">Reservasi</a>
        </div>
    </nav>
</header>

<main class="flex-1">
    @if (session('success'))
        <div class="container mx-auto px-4 mt-6">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>
        </div>
    @endif
    @yield('content')
</main>

<footer class="public-footer mt-20">
    <div class="container mx-auto px-4 py-8 text-sm text-slate-700">
        <p>(c) {{ now()->year }} {{ $globalSetting->site_name ?? 'MUA Portfolio' }}. Hak cipta dilindungi.</p>
    </div>
</footer>
</body>
</html>

