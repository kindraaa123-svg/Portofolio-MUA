@extends('layouts.public')
@section('title', 'Galeri Makeup')
@section('content')
<section class="container mx-auto px-4 py-16">
    <h1 class="font-serif text-4xl text-rose-900 mb-3">Galeri Foto Makeup</h1>
    <p class="text-rose-900/80 mb-8">Kumpulan detail hasil makeup dari berbagai look dan karakter acara.</p>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($images as $image)
            <a href="{{ $image->portfolio ? route('portfolio.show', $image->portfolio->slug) : '#' }}" class="block rounded-2xl overflow-hidden border border-rose-100 bg-white shadow-sm">
                <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->alt_text ?: 'Gallery Makeup' }}" class="h-48 w-full object-cover hover:scale-105 transition">
            </a>
        @empty
            <p class="col-span-full">Belum ada foto galeri.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $images->links() }}</div>
</section>
@endsection
