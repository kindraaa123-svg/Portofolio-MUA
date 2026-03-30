@extends('layouts.public')
@section('title', 'Testimoni')
@section('content')
<section class="container mx-auto px-4 py-16">
    <h1 class="font-serif text-4xl text-rose-900 mb-8">Testimoni Pelanggan</h1>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($testimonials as $item)
            <article class="card-premium">
                <p class="text-amber-500">{{ str_repeat('*', $item->rating) }}</p>
                <p class="mt-3 text-rose-900/80">{{ $item->message }}</p>
                <p class="mt-4 font-semibold">{{ $item->name }}</p>
            </article>
        @endforeach
    </div>
    <div class="mt-8">{{ $testimonials->links() }}</div>
</section>
@endsection
