@extends('layouts.master')

@section("title")

Product || Foco-art

@endsection

@section('content')

<section class="flex mx-auto max-w-screen-xl justify-between items-center w-full h-[90vh] gap-x-2 max-md:flex-col max-md:gap-y-2 max-md:justify-center max-md:mt-[120px] max-md:overflow-y-scroll max-md:h-full max-md:mb-12">
    <div class="flex flex-col justify-center max-w-screen-xl items-center w-1/2">
        <div class="flex border-stone-900 border-solid border-4">
            <img src="{{ $product->url }}" alt="" class="object-cover p-3">
        </div>
        <div class="text-center flex flex-col justify-center">
            <p>{{ $product->title }}</p>
            <p class="text-[13px] text-gray-400">Cijena: {{ $product->price }} KM</p>
        </div>
    </div>
    <div class="flex flex-col gap-y-6 justify-center max-w-[50%] max-md:w-1/2 max-md:max-w-full">
        <div class="max-md:flex max-md:justify-center">
            <a href="/shop" class="flex justify-center items-center ease-in duration-300 min-w-[120px] max-w-[150px] py-2 text-center border-solid border-2 border-stone-900 text-white bg-stone-900 hover:text-stone-900 hover:bg-white gap-x-3 lg:hidden">
                <svg stroke="currentColor" fill="#ccb17a" stroke-width="1" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                Naruci sliku
            </a>
        </div>
        <h1 class="text-[26px]">{{ $product->title }}</h1>
        <p>{{ $product->description }}</p>
        <p class="text-justify">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Tempora, dolores laboriosam aliquam atque voluptatem quibusdam explicabo vel sed nemo saepe? Praesentium aspernatur minus deleniti, molestiae laboriosam harum enim recusandae quaerat.</p>
        <div class="flex gap-x-1 flex-row flex-wrap">
            @foreach ($product->tags as $item)
                <p class="text-white bg-gray-700 hover:bg-gray-500 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">{{ $item->name }}</p>
            @endforeach
        </div>
        <a href="/shop" class="flex justify-center items-center ease-in duration-300 min-w-[120px] max-w-[150px] py-2 text-center border-solid border-2 border-stone-900 text-white bg-stone-900 hover:text-stone-900 hover:bg-white gap-x-3 max-md:hidden">
            <svg stroke="currentColor" fill="#ccb17a" stroke-width="1" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            Naruci sliku
        </a>
    </div>
</section>

@endsection