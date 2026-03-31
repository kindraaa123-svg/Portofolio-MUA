@extends('layouts.public')

@section('title', '404 - Halaman Tidak Ditemukan')
@section('meta_description', 'Halaman yang Anda cari tidak ditemukan.')

@section('content')
    <section class="container mx-auto px-4 py-20 md:py-28">
        <div class="mx-auto max-w-3xl card-premium text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-black">Error 404</p>
            <h1 class="mt-3 font-serif text-4xl text-black md:text-5xl">Halaman Tidak Ditemukan</h1>
            <p class="mt-5 text-base text-black md:text-lg">
                URL yang Anda akses tidak tersedia atau sudah dipindahkan.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-primary">Kembali ke Beranda</a>
            </div>
        </div>
    </section>
@endsection
