@extends('layouts.public')

@section('title', '403 - Akses Ditolak')
@section('meta_description', 'Anda tidak memiliki izin untuk mengakses halaman ini.')

@section('content')
    <section class="container mx-auto px-4 py-20 md:py-28">
        <div class="mx-auto max-w-3xl card-premium text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-black">Error 403</p>
            <h1 class="mt-3 font-serif text-4xl text-black md:text-5xl">Akses Ditolak</h1>
            <p class="mt-5 text-base text-black md:text-lg">
                Maaf, Anda tidak memiliki izin untuk membuka halaman ini.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-primary">Kembali ke Beranda</a>
            </div>
        </div>
    </section>
@endsection
