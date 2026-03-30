@extends('layouts.public')
@section('title', 'Pricelist Layanan')
@section('content')
<section class="container mx-auto px-4 py-16">
    <h1 class="font-serif text-4xl text-rose-900 mb-8">Pricelist & Add-on</h1>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @foreach ($serviceCategories as $category)
                <article class="card-premium">
                    <h2 class="font-semibold text-xl text-rose-900">{{ $category->name }}</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($category->services as $service)
                            <div class="flex justify-between border-b border-rose-100 pb-2">
                                <span>{{ $service->name }}</span>
                                <strong>Rp {{ number_format($service->price, 0, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
        <aside class="card-premium h-fit">
            <h2 class="font-semibold text-xl text-rose-900">Add-on</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($addons as $addon)
                    <li class="flex justify-between"><span>{{ $addon->name }}</span><strong>Rp {{ number_format($addon->price, 0, ',', '.') }}</strong></li>
                @endforeach
            </ul>
        </aside>
    </div>
</section>
@endsection
