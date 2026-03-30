@extends('layouts.public')
@section('title', 'Welcome')
@section('content')
<section class="container mx-auto px-4 py-16">
    <h1 class="font-serif text-4xl">{{ $globalSetting->site_name ?? 'MUA Portfolio' }}</h1>
</section>
@endsection
