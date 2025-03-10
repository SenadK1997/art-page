@extends('layouts.master')

@section("title")

Product || Foco-art

@endsection

@section('content')
<section class="flex mx-auto max-w-screen-xl mt-12 w-full gap-x-9 max-md:flex-col max-md:gap-y-2 max-md:justify-center max-md:pt-[20px] max-md:overflow-y-scroll max-md:items-center overflow-y-scroll">
    <div class="flex flex-col justify-center max-w-screen-xl items-center w-1/2 gap-y-3 mt-12 max-md:w-full max-md:p-5 max-md:mt-[15px]">
        <div class="border-[10px] rounded-[2px] relative border-y-[#000000] border-x-[#333333] okvir__slike" id="okvir">
            <div class="flex items-center relative">
                <img src="{{ asset('storage/images/' . $product->url) }}" alt="" 
                    class="object-cover max-h-[500px]">
                @if ($product->tags->contains('name', 'Orginal'))
                <div class="absolute right-2 top-2">
                    <span class="bg-yellow-500 rounded text-white px-2 py-1 text-s">Orginal</span>
                </div>
                @endif
            </div>
        </div>
        <div class="text-center flex flex-col justify-center">
            <p>{{ $product->title }}</p>
            {{-- @foreach ($product->images->sortBy('price') as $item) --}}
                <p class="text-[15px] text-gray-400" id="product-price">Cijena: {{ $product->images->min('price') ?? 'N/A' }} KM</p>
            {{-- @endforeach --}}
        </div>
    </div>
    <div class="flex flex-col gap-y-4 justify-center max-w-[50%] max-md:w-full max-md:justify-center max-md:max-w-full whitespace-normal break-words">
        <div class="flex flex-col gap-y-3 w-full mx-auto justify-center max-md:p-5">
            <h1 class="text-[26px]">{{ $product->title }}</h1>
            <p class="text-justify">{{ $product->description }}</p>
        </div>
        <div class="w-full justify-center md:justify-start flex gap-x-4">
            <div>
                <label for="color-selector" class="block font-medium mb-2 whitespace-nowrap">Izaberi okvir</label>
                <div class="relative">
                    <select id="color-selector" name="color-selector" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-2 px-3 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        {{-- @foreach ($product->images as $item) --}}
                        <option value="border-y-[#000000] border-x-[#333333]">Crni</option>
                        <option value="border-y-[#C0C0C0] border-x-[#D3D3D3]">Srebrni</option>
                        <option value="border-y-[#FFD700] border-x-[#FFDF80]">Zlatni</option>
                        <option value="border-y-[#B87333] border-x-[#E7AD7C]">Bakarni</option>
                        <option value="border-y-[#F2F2F2] border-x-[#FFFFFF]">Bijeli</option>
                        <option value="border-y-[#808080] border-x-[#A9A9A9]">Sivi</option>
                        <option value="border-y-[#964B00] border-x-[#A56B1F]">Smedji</option>
                        {{-- @endforeach --}}
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" viewBox="0 0 20 20"><path d="M14.71 7.29a1 1 0 0 0-1.42 0L10 10.59 6.71 7.29a1 1 0 0 0-1.42 1.42l4 4a1 1 0 0 0 1.42 0l4-4a1 1 0 0 0 0-1.42z"/></svg>
                    </div>
                </div>
            </div>
            <div>
                <label for="size-selector" class="block font-medium mb-2 whitespace-nowrap">Izaberi dimenziju</label>
                <div class="relative">
                    <select id="size-selector" name="color-selector" class="block appearance-none w-full bg-gray-100 border border-gray-200 text-gray-700 py-2 px-3 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                        @foreach ($product->images->sortBy('price') as $item)
                            <option value="{{ $item->price }}" data-width="{{ $item->width }}" data-height="{{ $item->height }}">{{ $item->width }} x {{ $item->height }} cm</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" viewBox="0 0 20 20"><path d="M14.71 7.29a1 1 0 0 0-1.42 0L10 10.59 6.71 7.29a1 1 0 0 0-1.42 1.42l4 4a1 1 0 0 0 1.42 0l4-4a1 1 0 0 0 0-1.42z"/></svg>
                    </div>
                </div>
            </div>
          </div>
        <div class="flex gap-x-1 flex-row flex-wrap max-md:w-full max-md:justify-center">
            @foreach ($product->tags as $item)
                <p class="text-white {{ $item->name === 'Orginal' ? 'bg-yellow-500' : 'bg-gray-700' }} bg-gray-700 hover:bg-gray-500 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 max-md:px-2 max-md:py-1.5 max-md:text-[12px]">
                    {{ $item->name }}
                </p>
            @endforeach
        </div>
        <form action="{{ route('shop.cart.add') }}" method="POST">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="product_url" value="{{ $product->url }}">
            <input type="hidden" name="product_title" value="{{ $product->title }}">
            <input type="hidden" name="product_width" value="{{ $product->images->min('width') }}">
            <input type="hidden" name="product_height" value="{{ $product->images->min('height') }}">
            <input type="hidden" name="product_description" value="{{ $product->description }}">
            <input type="hidden" name="product_price" value="{{ $product->images->min('price') ?? 0 }}">
            <input type="hidden" name="selected_option" id="selected-option" value="Srebrni">
            <input type="hidden" name="quantity" value="1" min="1">
            <button type="submit" class="flex px-2 rounded-lg justify-center items-center ease-in duration-300 min-w-[120px] max-w-[150px] py-2 text-center border-solid border-2 border-stone-900 text-white bg-stone-900 hover:text-stone-900 hover:bg-white gap-x-3 max-md:mx-auto max-md:mb-[50px]">
                <svg stroke="currentColor" fill="#ccb17a" stroke-width="1" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                Naruci sliku
            </button>
        </form>
    </div>
</section>
@endsection
@push('script')
    <script>
        $('#color-selector').on('change', function() {
            let color = $(this).val();
            let selectedOptionText = $(this).find('option:selected').text();
            let selectedOption = document.getElementById('selected-option');
            $('#okvir').removeClass().addClass('border-[10px] rounded-[2px] relative okvir__slike ' + color);
            // Promjena okvira u input hiddenu
            console.log(selectedOptionText);
            // $('#selected-option').val(selectedOptionText);
            selectedOption.value = selectedOptionText;
            console.log(selectedOption.value);
        });
        const sizeSelector = document.getElementById('size-selector');
        const priceDisplay = document.getElementById('product-price');
        const widthInput = document.querySelector('input[name="product_width"]');
        const heightInput = document.querySelector('input[name="product_height"]');

        sizeSelector.addEventListener('change', (event) => {
            const selectedOption = sizeSelector.options[sizeSelector.selectedIndex];
            const newPrice = selectedOption.value;
            const width = selectedOption.dataset.width;
            const height = selectedOption.dataset.height;

            const priceInput = document.querySelector('input[name="product_price"]');
            priceInput.value = newPrice;
            priceDisplay.innerText = 'Cijena:' + newPrice + ' KM';
            widthInput.value = width;
            heightInput.value = height;
        });
    </script>
@endpush