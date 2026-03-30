@extends('layouts.public')

@section('title', 'Beranda | ' . ($globalSetting->site_name ?? 'Aurora Beauty MUA'))

@section('content')
<section class="hero-section py-20" @if(!empty($globalSetting?->home_banner)) style="background-image:linear-gradient(rgba(255,247,250,.86),rgba(255,255,255,.95)),url('{{ asset('storage/' . $globalSetting->home_banner) }}');background-size:cover;background-position:center;" @endif>
    <div class="container mx-auto px-4 grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <p class="uppercase tracking-[0.25em] text-xs text-rose-600 mb-4">Premium Makeup Artist</p>
            <h1 class="font-serif text-4xl md:text-6xl leading-tight text-rose-900">Elegan, modern, feminin untuk momen paling spesial.</h1>
            <p class="mt-6 text-rose-900/80 max-w-xl">{{ $globalSetting?->tagline ?: 'Layanan makeup profesional untuk wedding, wisuda, engagement, party, prewedding, hingga editorial dengan sentuhan personal dan detail premium.' }}</p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('booking.create') }}" class="btn-primary">Reservasi Sekarang</a>
                <a href="{{ route('portfolio.index') }}" class="btn-secondary">Lihat Portfolio</a>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            @foreach ($featuredPortfolios->take(4) as $item)
                <div class="rounded-2xl overflow-hidden shadow-xl border border-rose-100 bg-white h-44">
                    @if ($item->cover_image)
                        <img src="{{ asset('storage/' . $item->cover_image) }}" class="h-full w-full object-cover" alt="{{ $item->title }}">
                    @else
                        <div class="h-full w-full bg-rose-100/80"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 container mx-auto px-4">
    <div class="flex items-center justify-between gap-4 mb-8">
        <h2 class="font-serif text-3xl text-rose-900">Layanan Utama</h2>
        <a href="{{ route('pricelist') }}" class="btn-secondary">Lihat Semua Harga</a>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($serviceCategories as $category)
            <article class="card-premium">
                <h3 class="font-semibold text-lg text-rose-900">{{ $category->name }}</h3>
                <div class="mt-3 space-y-2 text-sm text-rose-900/80">
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

<section class="py-16 bg-rose-100/70">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between gap-4 mb-8">
            <h2 class="font-serif text-3xl text-rose-900">Galeri Makeup</h2>
            <a href="{{ route('gallery') }}" class="btn-secondary">Buka Galeri</a>
        </div>
        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($featuredPortfolios->take(8) as $item)
                <a href="{{ route('portfolio.show', $item->slug) }}" class="block rounded-2xl overflow-hidden h-52 border border-rose-200 bg-white">
                    @if ($item->cover_image)
                        <img src="{{ asset('storage/' . $item->cover_image) }}" class="h-full w-full object-cover hover:scale-105 transition" alt="{{ $item->title }}">
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 container mx-auto px-4">
    <h2 class="font-serif text-3xl text-rose-900 mb-8">Testimoni Pelanggan</h2>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($testimonials as $item)
            <article class="card-premium">
                <p class="text-amber-500">{{ str_repeat('*', $item->rating) }}</p>
                <p class="mt-4 text-rose-900/80">"{{ $item->message }}"</p>
                <p class="mt-4 font-semibold text-rose-900">{{ $item->name }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="py-16 bg-white/80 border-y border-rose-100">
    <div class="container mx-auto px-4 max-w-4xl">
        <h2 class="font-serif text-3xl text-rose-900 mb-8">FAQ</h2>
        <div class="space-y-4">
            @foreach ($faqs as $faq)
                <details class="card-premium">
                    <summary class="font-semibold cursor-pointer">{{ $faq->question }}</summary>
                    <p class="mt-3 text-rose-900/80">{{ $faq->answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endsection
