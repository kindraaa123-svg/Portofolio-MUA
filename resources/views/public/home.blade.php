@extends('layouts.public')

@section('title', 'Beranda | ' . ($globalSetting->site_name ?? 'Aurora Beauty MUA'))

@section('content')
<section class="py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <div class="grid items-stretch gap-0 lg:grid-cols-2">
                <div class="bg-white p-6 md:p-10 lg:p-12">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-primary);">Premium Makeup Artist</p>
                    <h1 class="font-serif text-4xl leading-tight md:text-6xl" style="color: var(--theme-primary);">Tampilan elegan untuk momen paling penting.</h1>
                    <p class="mt-6 max-w-xl text-slate-700">{{ $globalSetting?->tagline ?: 'Layanan makeup profesional untuk wedding, wisuda, engagement, party, prewedding, hingga editorial dengan sentuhan personal dan detail premium.' }}</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('booking.create') }}" class="btn-primary">Reservasi Sekarang</a>
                        <a href="{{ route('portfolio.index') }}" class="btn-secondary">Lihat Portfolio</a>
                    </div>
                </div>

                <div class="relative min-h-[300px] overflow-hidden lg:min-h-[520px]" style="background-color: var(--theme-primary);">
                    @if (!empty($globalSetting?->home_banner))
                        <img src="{{ asset('storage/' . $globalSetting->home_banner) }}" class="h-full w-full object-cover" alt="Banner {{ $globalSetting?->site_name ?? 'MUA' }}">
                    @else
                        <div class="flex h-full items-center justify-center p-8 text-center">
                            <p class="font-serif text-2xl text-white/90">Beauty that feels like you.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pb-12 md:pb-16">
    <div class="container mx-auto px-4">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Our Services</p>
                <h2 class="font-serif text-3xl" style="color: var(--theme-primary);">Layanan Utama</h2>
            </div>
            <a href="{{ route('pricelist') }}" class="btn-secondary">Lihat Semua Harga</a>
        </div>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($serviceCategories as $category)
                <article class="card-premium h-full">
                    <h3 class="text-lg font-semibold" style="color: var(--theme-primary);">{{ $category->name }}</h3>
                    <div class="mt-4 space-y-2 text-sm text-slate-700">
                        @forelse ($category->services->take(4) as $service)
                            <div class="flex items-start justify-between gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                <span>{{ $service->name }}</span>
                                <strong>Rp {{ number_format($service->price, 0, ',', '.') }}</strong>
                            </div>
                        @empty
                            <p class="text-slate-500">Belum ada layanan pada kategori ini.</p>
                        @endforelse
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

@if($addons->isNotEmpty())
<section class="pb-12 md:pb-16">
    <div class="container mx-auto px-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 md:p-8">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Extra Touch</p>
                    <h2 class="font-serif text-3xl" style="color: var(--theme-primary);">Add-on Favorit</h2>
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($addons->take(6) as $addon)
                    <div class="addon-item">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $addon->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $addon->description ?: 'Add-on premium untuk menyempurnakan look Anda.' }}</p>
                        </div>
                        <strong>Rp {{ number_format($addon->price, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<section class="bg-white py-14 md:py-16 border-y" style="border-color: var(--theme-secondary);">
    <div class="container mx-auto px-4">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Recent Work</p>
                <h2 class="home-gallery-title font-serif text-3xl">Galeri Makeup</h2>
            </div>
            <a href="{{ route('gallery') }}" class="btn-secondary home-gallery-link">Buka Galeri</a>
        </div>
        <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($featuredPortfolios->take(8) as $item)
                @php
                    $coverImagePath = $item->cover_image ?: $item->images->first()?->image_path;
                    $coverImagePath = $coverImagePath ? preg_replace('#^/?storage/#', '', $coverImagePath) : null;
                @endphp
                <a href="{{ route('portfolio.show', $item->slug) }}" class="gallery-tile">
                    @if ($coverImagePath)
                        <img src="{{ asset('storage/' . ltrim($coverImagePath, '/')) }}" class="h-full w-full object-cover transition duration-300 hover:scale-105" alt="{{ $item->title }}">
                    @else
                        <div class="grid h-full w-full place-items-center text-sm font-medium text-slate-500">Foto belum tersedia</div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>

@if($testimonials->isNotEmpty())
<section class="py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Client Voices</p>
                <h2 class="font-serif text-3xl" style="color: var(--theme-primary);">Apa Kata Klien</h2>
            </div>
            <a href="{{ route('testimonials') }}" class="btn-secondary">Lihat Semua</a>
        </div>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach($testimonials->take(6) as $item)
                <article class="card-premium h-full">
                    <p class="text-sm leading-relaxed text-slate-700">"{{ $item->message }}"</p>
                    <div class="mt-4 border-t border-slate-200 pt-3">
                        <p class="font-semibold text-slate-800">{{ $item->name }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-12 md:py-16">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Need Help?</p>
            <h2 class="font-serif text-3xl" style="color: var(--theme-primary);">FAQ</h2>
        </div>
        <div class="space-y-4">
            @foreach ($faqs as $faq)
                <details class="card-premium">
                    <summary class="cursor-pointer font-semibold">{{ $faq->question }}</summary>
                    <p class="mt-3 text-slate-700">{{ $faq->answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

<section class="pb-16">
    <div class="container mx-auto px-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center md:p-10">
            <h2 class="font-serif text-3xl" style="color: var(--theme-primary);">Siap Tampil Maksimal?</h2>
            <p class="mx-auto mt-3 max-w-2xl text-slate-600">Konsultasikan kebutuhan makeup Anda, lalu jadwalkan sesi terbaik sesuai acara.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('booking.create') }}" class="btn-primary">Reservasi Sekarang</a>
                <a href="{{ route('contact') }}" class="btn-secondary">Hubungi Kami</a>
            </div>
        </div>
    </div>
</section>
@endsection
