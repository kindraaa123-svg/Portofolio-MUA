@extends('layouts.public')
@section('title', 'FAQ')
@section('content')
<section class="container mx-auto px-4 py-16 max-w-4xl">
    <h1 class="font-serif text-4xl text-black mb-8">Frequently Asked Questions</h1>
    <div class="space-y-4">
        @foreach ($faqs as $faq)
            <details class="card-premium">
                <summary class="font-semibold cursor-pointer">{{ $faq->question }}</summary>
                <p class="mt-3 text-black">{{ $faq->answer }}</p>
            </details>
        @endforeach
    </div>
</section>
@endsection
