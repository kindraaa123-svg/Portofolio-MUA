@extends('layouts.public')
@section('title', 'Kontak & Lokasi')
@section('content')
<section class="container mx-auto px-4 py-16 max-w-4xl">
    <h1 class="font-serif text-4xl text-black">Kontak & Lokasi</h1>
    <div class="mt-8 grid md:grid-cols-2 gap-6">
        <article class="card-premium">
            <h2 class="font-semibold text-xl">Hubungi Kami</h2>
            <p class="mt-3">WhatsApp: {{ $globalSetting->contact_phone ?? '-' }}</p>
            <p>Email: {{ $globalSetting->contact_email ?? '-' }}</p>
            <p>Instagram: {{ $globalSetting->instagram_url ?? '-' }}</p>
        </article>
        <article class="card-premium">
            <h2 class="font-semibold text-xl">Alamat Studio</h2>
            <p class="mt-3">{{ $globalSetting->address ?? 'Alamat belum diatur.' }}</p>
        </article>
    </div>
</section>
@endsection
