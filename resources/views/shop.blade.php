@extends('layouts.master')

@section("title")

Shop || Foco-art

@endsection

@section('content')

<section class="flex flex-col justify-center w-full p-4 mx-auto max-w-screen-xl items-center mt-[80px] max-md:justify-center gap-x-[100px] max-md:mt-[40px]">
    <div class="flex max-w-screen-xl mx-auto items-center w-full m-10 justify-between max-md:flex-col max-md:gap-y-4">
        <form class="flex w-full max-w-screen-xl pt-[10px]" type="get" action="{{ url('/shop') }}">
            <div class="flex w-full max-w-screen-xl justify-between items-center gap-x-5">
                <input type="search" class="mr-sm-2 p-2 border-gray-200 border-b-[2px] w-full outline-none" placeholder="Search" name="query">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Trazi</button>
            </div>    
        </form>
        <div class="flex items-center">
            <div class="flex flex-row overflow-x-scroll items-center justify-center">
                <button id="dropdownBgHoverButton" data-dropdown-toggle="dropdownBgHover" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
                    Kategorije
                    <svg class="w-4 h-4 ml-2" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <!-- Dropdown menu -->
                <div id="dropdownBgHover" class="z-10 hidden w-48 bg-white rounded-lg shadow dark:bg-gray-700">
                    <ul class="max-h-[550px] h-full overflow-y-scroll max-md:h-[350px] p-3 space-y-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownBgHoverButton">
                        <form type="get" action="{{ url('/shop') }}" method="get" action="submit">
                            <button type="submit" class="flex items-center font-bold gap-x-2 w-full ml-2 text-sm text-gray-900 rounded dark:text-gray-300 mb-2">
                                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 16 16" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 10.5a.5.5 0 01.5-.5h3a.5.5 0 010 1h-3a.5.5 0 01-.5-.5zm-2-3a.5.5 0 01.5-.5h7a.5.5 0 010 1h-7a.5.5 0 01-.5-.5zm-2-3a.5.5 0 01.5-.5h11a.5.5 0 010 1h-11a.5.5 0 01-.5-.5z" clip-rule="evenodd"></path></svg>    
                                Filtriraj
                            </button>                
                            @foreach ($tags as $tag)
                                <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <input id="{{ $tag->id }}" type="checkbox" value="{{$tag->id}}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500" 
                                    name="qtags[{{ $tag->id }}]" @if(isset($_GET['qtags']) && in_array($tag->id, $_GET['qtags'])) checked @endif>
                                    <label for="{{ $tag->id }}" class="w-full ml-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">{{ $tag->name }}</label>
                                </div>
                            @endforeach
                            <a href="/shop" class="flex text-[16px] items-center gap-x-2 w-full ml-2 font-medium text-gray-900 rounded dark:text-gray-300 mt-2">
                                <svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1" viewBox="0 0 48 48" enable-background="new 0 0 48 48" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path fill="#B39DDB" d="M30.6,44H17.4c-2,0-3.7-1.4-4-3.4L9,11h30l-4.5,29.6C34.2,42.6,32.5,44,30.6,44z"></path><path fill="#7E57C2" d="M38,13H10c-1.1,0-2-0.9-2-2v0c0-1.1,0.9-2,2-2h28c1.1,0,2,0.9,2,2v0C40,12.1,39.1,13,38,13z"></path></svg>
                                Ocisti filtere
                            </a>     
                        </form>
                    </ul>
                </div>
            </div>
        </div>
    </div>
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
                {{-- <p>{{ $tags }}</p> --}}
            </div>
        </div>
        @endforeach
    </div>
    <div class="flex gap-x-5">
        {{ $items->links() }}
    </div>
</section>
@endsection