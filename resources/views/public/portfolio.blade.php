@extends('layouts.public')
@section('title', 'Portfolio Makeup')
@section('content')
<section class="container mx-auto px-4 py-16">
    <h1 class="font-serif text-4xl text-rose-900 mb-6">Portfolio Makeup</h1>
    <div class="flex flex-wrap gap-3 mb-8">
        <a href="{{ route('portfolio.index') }}" class="chip {{ $activeCategory ? '' : 'chip-active' }}">Semua</a>
        @foreach ($categories as $category)
            <a href="{{ route('portfolio.index', ['category' => $category->slug]) }}" class="chip {{ $activeCategory === $category->slug ? 'chip-active' : '' }}">{{ $category->name }}</a>
        @endforeach
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($portfolios as $item)
            <a href="{{ route('portfolio.show', $item->slug) }}" class="card-premium group">
                <div class="h-56 rounded-xl overflow-hidden bg-rose-100">
                    @if ($item->cover_image)
                        <img src="{{ asset('storage/' . $item->cover_image) }}" class="h-full w-full object-cover group-hover:scale-105 transition" alt="{{ $item->title }}">
                    @endif
                </div>
                <h3 class="mt-4 font-semibold text-lg text-rose-900">{{ $item->title }}</h3>
                <p class="text-sm text-rose-700">{{ $item->category?->name }}</p>
            </a>
        @empty
            <p>Belum ada portfolio.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $portfolios->links() }}</div>
</section>
@endsection
