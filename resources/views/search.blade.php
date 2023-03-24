@extends('layouts.master')

@section("title")

Shop || Foco-art

@endsection

@section('content')

<section class="flex flex-col justify-center w-full p-4 mx-auto max-w-screen-xl items-center mt-[80px] max-md:justify-center gap-x-[100px] max-md:mt-[40px]">
    <form class="flex w-full max-w-screen-xl p-10" type="get" action="{{ url('/search') }}">
        <div class="flex w-full max-w-screen-xl">
            <input type="search" class="mr-sm-2 p-2 border-gray-200 border-b-[2px] min-w-full outline-none" placeholder="Search" name="query">
            <button type="submit"></button>
        </form>
    </div>
    {{-- <div class="flex w-full">
        @foreach ($item->tags as $tag)
        <button>{{ $tag }}</button>
        @endforeach
    </div> --}}
    <div class="flex flex-row flex-wrap justify-center w-full p-4 mx-auto max-w-screen-xl items-center max-md:justify-center gap-x-[100px] gap-y-6">
        @foreach ($items as $item)
        <div class="flex flex-col">
            <a href="/product/{{$item->id}}" class="flex flex-col border-stone-900 border-solid border-4">
                <div class="relative flex flex-col bg-white h-80 w-52">
                    <div class="flex justify-center mx-auto items-center h-full">
                        <img src="{{$item->url}}" alt="" class="w-full h-full object-cover p-2">
                    </div>
                </div>
            </a>
            <div class="text-center flex flex-col justify-center">
                <p>{{ $item->title }}</p>
                <p class="text-[13px] text-gray-400">Cijena: {{ $item->price }} KM</p>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endsection