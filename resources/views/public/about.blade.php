@extends('layouts.public')
@section('title', 'Tentang Kami')
@section('content')
<section class="container mx-auto px-4 py-16 max-w-4xl">
    <h1 class="font-serif text-4xl text-rose-900">Tentang Kami</h1>
    <p class="mt-6 text-rose-900/80 leading-8">{{ $globalSetting->site_name ?? 'Aurora Beauty MUA' }} adalah studio makeup profesional dengan fokus pada hasil flawless, higienis, dan tahan lama. Kami melayani makeup wedding, wisuda, engagement, party, prewedding, dan editorial.</p>
</section>
@endsection
