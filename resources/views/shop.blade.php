@extends('layouts.master')

@section("title")

Shop || Foco-art

@endsection

@section('content')

<section class="flex flex-col mx-auto max-w-screen-xl items-center mt-[120px]">
    <div class="flex flex-col">
        <div class="relative flex flex-col bg-gray-400 h-80 w-52">
            <div class="flex justify-center mx-auto items-center h-full">
                <img src="https://dummyimage.com/150x200/ff00ff/0011ff&text=dummy+product" alt="">
            </div>
            <div class="absolute bottom-1 right-1">
                <p>Na stanju: 10</p>
            </div>
        </div>
        <div class="text-center flex justify-center">
            <p>Informacije o produktu</p>
        </div>
    </div>
</section>

@endsection