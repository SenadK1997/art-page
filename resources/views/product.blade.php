@extends('layouts.master')

@section("title")

Product || Foco-art

@endsection

@section('content')

<section class="flex mx-auto max-w-screen-xl items-center justify-center w-full h-[90vh] gap-x-24 max-md:flex-col max-md:gap-y-4 max-md:mt-[40px]">
    <div class="flex flex-col justify-center">
        <div class="border-stone-900 border-solid border-4">
            <img src="{{ $product->url }}" alt="" class="object-cover p-3">
        </div>
        <div class="text-center flex flex-col justify-center">
            <p>{{ $product->title }}</p>
            <p class="text-[13px] text-gray-400">Cijena: {{ $product->price }} KM</p>
        </div>
    </div>
    <div class="flex flex-col gap-y-6">
        <h1 class="text-[26px]">{{ $product->title }}</h1>
        <p>{{ $product->description }}</p>
        <div>Tags</div>
        <a href="/shop" class="flex justify-center items-center ease-in duration-300 min-w-[120px] max-w-[150px] py-2 text-center border-solid border-2 border-stone-900 text-white bg-stone-900 hover:text-stone-900 hover:bg-white gap-x-3">
            <svg stroke="currentColor" fill="#ccb17a" stroke-width="1" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            Naruci sliku
        </a>
    </div>
</section>

@endsection