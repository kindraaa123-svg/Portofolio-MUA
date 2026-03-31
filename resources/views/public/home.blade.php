@extends('layouts.public')

@section('title', 'Beranda | ' . ($globalSetting->site_name ?? 'Aurora Beauty MUA'))

@section('content')
<section class="hero-section py-20">
    <div class="container mx-auto px-4 grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <p class="uppercase tracking-[0.25em] text-xs mb-4" style="color: var(--theme-primary);">Premium Makeup Artist</p>
            <h1 class="font-serif text-4xl md:text-6xl leading-tight" style="color: var(--theme-primary);">Elegan, modern, feminin untuk momen paling spesial.</h1>
            <p class="mt-6 text-slate-700 max-w-xl">{{ $globalSetting?->tagline ?: 'Layanan makeup profesional untuk wedding, wisuda, engagement, party, prewedding, hingga editorial dengan sentuhan personal dan detail premium.' }}</p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('booking.create') }}" class="btn-primary">Reservasi Sekarang</a>
                <a href="{{ route('portfolio.index') }}" class="btn-secondary">Lihat Portfolio</a>
            </div>
        </div>
        <div class="rounded-3xl overflow-hidden shadow-xl h-72 lg:h-96" style="background-color: var(--theme-primary);">
            @if (!empty($globalSetting?->home_banner))
                <img src="{{ asset('storage/' . $globalSetting->home_banner) }}" class="h-full w-full object-cover" alt="Banner {{ $globalSetting?->site_name ?? 'MUA' }}">
            @endif
        </div>
    </div>
</section>

<section class="py-16 container mx-auto px-4">
    <div class="flex items-center justify-between gap-4 mb-8">
        <h2 class="font-serif text-3xl" style="color: var(--theme-primary);">Layanan Utama</h2>
        <a href="{{ route('pricelist') }}" class="btn-secondary">Lihat Semua Harga</a>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($serviceCategories as $category)
            <article class="card-premium">
                <h3 class="font-semibold text-lg" style="color: var(--theme-primary);">{{ $category->name }}</h3>
                <div class="mt-3 space-y-2 text-sm text-slate-700">
                    @foreach ($category->services->take(3) as $service)
                        <div class="flex justify-between gap-4">
                            <span>{{ $service->name }}</span>
                            <strong>Rp {{ number_format($service->price, 0, ',', '.') }}</strong>
                        </div>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>
</section>

<section class="py-16" style="background-color: var(--theme-secondary);">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between gap-4 mb-8">
            <h2 class="font-serif text-3xl" style="color: var(--theme-primary);">Galeri Makeup</h2>
            <a href="{{ route('gallery') }}" class="btn-secondary">Buka Galeri</a>
        </div>
        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($featuredPortfolios->take(8) as $item)
                @php
                    $coverImagePath = $item->cover_image ?: $item->images->first()?->image_path;
                    $coverImagePath = $coverImagePath ? preg_replace('#^/?storage/#', '', $coverImagePath) : null;
                @endphp
                <a href="{{ route('portfolio.show', $item->slug) }}" class="block rounded-2xl overflow-hidden h-52 border bg-white" style="border-color: var(--theme-secondary);">
                    @if ($coverImagePath)
                        <img src="{{ asset('storage/' . ltrim($coverImagePath, '/')) }}" class="h-full w-full object-cover hover:scale-105 transition" alt="{{ $item->title }}">
                    @else
                        <div class="h-full w-full grid place-items-center text-sm font-medium text-slate-500">
                            Foto belum tersedia
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-white/80 border-y" style="border-color: var(--theme-secondary);">
    <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="font-serif text-3xl mb-8" style="color: var(--theme-primary);">FAQ</h2>
        <div class="space-y-4">
            @foreach ($faqs as $faq)
                <details class="card-premium">
                    <summary class="font-semibold cursor-pointer">{{ $faq->question }}</summary>
                    <p class="mt-3 text-slate-700">{{ $faq->answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endsection
