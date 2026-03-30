@extends('layouts.public')
@section('title', $portfolio->title)
@section('content')
<section class="container mx-auto px-4 py-16">
    <h1 class="font-serif text-4xl text-rose-900">{{ $portfolio->title }}</h1>
    <p class="mt-2 text-rose-700">{{ $portfolio->category?->name }} • {{ $portfolio->work_date?->format('d M Y') }}</p>
    <p class="mt-6 text-rose-900/80 max-w-3xl">{{ $portfolio->description }}</p>

    <div class="mt-10 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($portfolio->images as $image)
            <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-72 rounded-2xl object-cover" alt="{{ $image->alt_text ?? $portfolio->title }}">
        @empty
            @if($portfolio->cover_image)
                <img src="{{ asset('storage/' . $portfolio->cover_image) }}" class="w-full h-72 rounded-2xl object-cover" alt="{{ $portfolio->title }}">
            @endif
        @endforelse
    </div>
</section>
@endsection
