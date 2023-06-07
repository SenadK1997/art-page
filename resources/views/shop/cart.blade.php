@extends('layouts.carts')
@section("title")

Cart || Foco-art

@endsection

@section('content')
<div class="container mx-auto py-8 p-4">
    <h1 class="text-3xl font-bold mb-4">My Cart</h1>
    <div class="mt-4 mb-4">
        <a href="/shop" class="inline-block px-4 py-2 text-white bg-blue-500 hover:bg-blue-700 rounded-md transition-colors duration-300 ease-in-out">
            Back to Shop
        </a>
    </div>
  <div class="flex max-w-screen-xl mx-auto w-full gap-x-4 justify-between max-md:flex-col max-md:gap-y-5">
    <div class="flex flex-col max-w-[540px] w-full gap-y-6">
      @if (Session::has('error'))
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline">{{ Session::get('error') }}</span>
      </div>
    @endif

    @if (Session::has('success'))
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success:</strong>
        <span class="block sm:inline">{{ Session::get('success') }}</span>
      </div>
    @endif
      <div class="pb-3">
        <h1 class="text-3xl font-bold text-gray-800 mt-8">Your items:</h1>
      </div>
      {{-- <div class="relative"> --}}
        @if (Cart::content()->isEmpty())
          <div class="p-6 border">
            <h1>Your cart is empty.</h1>
          </div>
        @endif
        @if (session('success'))
          <?php Cart::destroy(); ?>
        @endif
        @foreach (Cart::content() as $item)
        <div class="flex flex-col justify-center">
          <div class="relative flex flex-col md:flex-row md:space-x-5 space-y-3 md:space-y-0 rounded-xl shadow-lg p-3 max-w-xs md:max-w-3xl mx-auto border border-white bg-white">
            <div class="w-full md:w-1/3 bg-white grid place-items-center">
              <img src="{{ asset('storage/images/'.$item->options->url) }}" alt="tailwind logo" class="rounded-xl" />
            </div>
              <div class="w-full md:w-2/3 bg-white flex flex-col space-y-2 p-3 gap-y-5">
                <div class="flex justify-between item-center">
                  <h3 class="font-black text-gray-800 md:text-3xl text-xl">{{ $item->name }}</h3>
                  <form action="{{ route('shop.cart.remove', $item->rowId) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700">Remove</button>
                  </form>
                </div>
                <div class="flex justify-between w-full">
                  <div class="text-sm font-medium flex items-center gap-x-2">
                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                      <path d="M160 288h-56c-4.42 0-8-3.58-8-8v-16c0-4.42 3.58-8 8-8h56v-64h-56c-4.42 0-8-3.58-8-8v-16c0-4.42 3.58-8 8-8h56V96h-56c-4.42 0-8-3.58-8-8V72c0-4.42 3.58-8 8-8h56V32c0-17.67-14.33-32-32-32H32C14.33 0 0 14.33 0 32v448c0 2.77.91 5.24 1.57 7.8L160 329.38V288zm320 64h-32v56c0 4.42-3.58 8-8 8h-16c-4.42 0-8-3.58-8-8v-56h-64v56c0 4.42-3.58 8-8 8h-16c-4.42 0-8-3.58-8-8v-56h-64v56c0 4.42-3.58 8-8 8h-16c-4.42 0-8-3.58-8-8v-56h-41.37L24.2 510.43c2.56.66 5.04 1.57 7.8 1.57h448c17.67 0 32-14.33 32-32v-96c0-17.67-14.33-32-32-32z"></path>
                    </svg>
                    {{ $item->options->width }} x {{ $item->options->height }}
                  </div>
                  <div class="text-sm">
                    <p class="font-medium">Okvir: {{ $item->options->frame }}</p>
                  </div>
                </div>                
                <p class="md:text-lg text-gray-500 text-base">{{ $item->options->description }}</p>
                <div class="flex justify-between item-center">
                  <div class="flex items-center">
                    <form action="{{ route('shop.cart.update', $item->rowId) }}" method="POST" class="flex h-full">
                      @csrf
                      <input type="hidden" name="product_qty" value="{{ $item->qty - 1 }}">
                      <button type="submit" class="px-2 text-blue-500 hover:text-blue-700">-</button>
                    </form>
                    <span class="px-2 h-full">{{ $item->qty }}</span>
                    <form action="{{ route('shop.cart.update', $item->rowId) }}" method="POST" class="flex h-full">
                      @csrf
                      <input type="hidden" name="product_qty" value="{{ $item->qty + 1}}">
                      <button type="submit" class="px-2 text-blue-500 hover:text-blue-700">+</button>
                    </form>
                  </div>
                  <p class="text-xl font-black text-gray-800">
                    {{ $item->options->price * $item->qty }} KM
                  </p>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      {{-- </div> --}}
      
    </div>
    <form action="{{ route('request.payment') }}" method="POST" class="flex flex-col border border-[2px] max-w-[540px] w-full p-4 bg-gray-200 rounded-xl gap-y-5 h-fit">
    @csrf
      <div>
        <h1 class="text-3xl">Suma</h1>
          <div class="flex w-full justify-between">
            <p>Broj narucenih artikala:</p>
            <p>{{ Cart::count() }}</p>
          </div>
          <div class="flex w-full justify-between">
            <h2 class="text-xl text-gray-500">Ukupno:</h2>
            <h2 class="text-blue-500">{{ Cart::subtotal() }} KM</h2>
            <input type="hidden" name="price" value="{{ Cart::subtotal() }}">
          </div>
          <div class="border-b border-gray-500"></div>
          {{-- <div class="flex flex-col">
            <h2>Nacin placanja:</h2>
            <div>
              <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 576 512" height="5em" width="5em" xmlns="http://www.w3.org/2000/svg"><path d="M186.3 258.2c0 12.2-9.7 21.5-22 21.5-9.2 0-16-5.2-16-15 0-12.2 9.5-22 21.7-22 9.3 0 16.3 5.7 16.3 15.5zM80.5 209.7h-4.7c-1.5 0-3 1-3.2 2.7l-4.3 26.7 8.2-.3c11 0 19.5-1.5 21.5-14.2 2.3-13.4-6.2-14.9-17.5-14.9zm284 0H360c-1.8 0-3 1-3.2 2.7l-4.2 26.7 8-.3c13 0 22-3 22-18-.1-10.6-9.6-11.1-18.1-11.1zM576 80v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h480c26.5 0 48 21.5 48 48zM128.3 215.4c0-21-16.2-28-34.7-28h-40c-2.5 0-5 2-5.2 4.7L32 294.2c-.3 2 1.2 4 3.2 4h19c2.7 0 5.2-2.9 5.5-5.7l4.5-26.6c1-7.2 13.2-4.7 18-4.7 28.6 0 46.1-17 46.1-45.8zm84.2 8.8h-19c-3.8 0-4 5.5-4.2 8.2-5.8-8.5-14.2-10-23.7-10-24.5 0-43.2 21.5-43.2 45.2 0 19.5 12.2 32.2 31.7 32.2 9 0 20.2-4.9 26.5-11.9-.5 1.5-1 4.7-1 6.2 0 2.3 1 4 3.2 4H200c2.7 0 5-2.9 5.5-5.7l10.2-64.3c.3-1.9-1.2-3.9-3.2-3.9zm40.5 97.9l63.7-92.6c.5-.5.5-1 .5-1.7 0-1.7-1.5-3.5-3.2-3.5h-19.2c-1.7 0-3.5 1-4.5 2.5l-26.5 39-11-37.5c-.8-2.2-3-4-5.5-4h-18.7c-1.7 0-3.2 1.8-3.2 3.5 0 1.2 19.5 56.8 21.2 62.1-2.7 3.8-20.5 28.6-20.5 31.6 0 1.8 1.5 3.2 3.2 3.2h19.2c1.8-.1 3.5-1.1 4.5-2.6zm159.3-106.7c0-21-16.2-28-34.7-28h-39.7c-2.7 0-5.2 2-5.5 4.7l-16.2 102c-.2 2 1.3 4 3.2 4h20.5c2 0 3.5-1.5 4-3.2l4.5-29c1-7.2 13.2-4.7 18-4.7 28.4 0 45.9-17 45.9-45.8zm84.2 8.8h-19c-3.8 0-4 5.5-4.3 8.2-5.5-8.5-14-10-23.7-10-24.5 0-43.2 21.5-43.2 45.2 0 19.5 12.2 32.2 31.7 32.2 9.3 0 20.5-4.9 26.5-11.9-.3 1.5-1 4.7-1 6.2 0 2.3 1 4 3.2 4H484c2.7 0 5-2.9 5.5-5.7l10.2-64.3c.3-1.9-1.2-3.9-3.2-3.9zm47.5-33.3c0-2-1.5-3.5-3.2-3.5h-18.5c-1.5 0-3 1.2-3.2 2.7l-16.2 104-.3.5c0 1.8 1.5 3.5 3.5 3.5h16.5c2.5 0 5-2.9 5.2-5.7L544 191.2v-.3zm-90 51.8c-12.2 0-21.7 9.7-21.7 22 0 9.7 7 15 16.2 15 12 0 21.7-9.2 21.7-21.5.1-9.8-6.9-15.5-16.2-15.5z"></path></svg>
            </div>
            <div>
              <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 576 512" height="5em" width="5em" xmlns="http://www.w3.org/2000/svg"><path d="M482.9 410.3c0 6.8-4.6 11.7-11.2 11.7-6.8 0-11.2-5.2-11.2-11.7 0-6.5 4.4-11.7 11.2-11.7 6.6 0 11.2 5.2 11.2 11.7zm-310.8-11.7c-7.1 0-11.2 5.2-11.2 11.7 0 6.5 4.1 11.7 11.2 11.7 6.5 0 10.9-4.9 10.9-11.7-.1-6.5-4.4-11.7-10.9-11.7zm117.5-.3c-5.4 0-8.7 3.5-9.5 8.7h19.1c-.9-5.7-4.4-8.7-9.6-8.7zm107.8.3c-6.8 0-10.9 5.2-10.9 11.7 0 6.5 4.1 11.7 10.9 11.7 6.8 0 11.2-4.9 11.2-11.7 0-6.5-4.4-11.7-11.2-11.7zm105.9 26.1c0 .3.3.5.3 1.1 0 .3-.3.5-.3 1.1-.3.3-.3.5-.5.8-.3.3-.5.5-1.1.5-.3.3-.5.3-1.1.3-.3 0-.5 0-1.1-.3-.3 0-.5-.3-.8-.5-.3-.3-.5-.5-.5-.8-.3-.5-.3-.8-.3-1.1 0-.5 0-.8.3-1.1 0-.5.3-.8.5-1.1.3-.3.5-.3.8-.5.5-.3.8-.3 1.1-.3.5 0 .8 0 1.1.3.5.3.8.3 1.1.5s.2.6.5 1.1zm-2.2 1.4c.5 0 .5-.3.8-.3.3-.3.3-.5.3-.8 0-.3 0-.5-.3-.8-.3 0-.5-.3-1.1-.3h-1.6v3.5h.8V426h.3l1.1 1.4h.8l-1.1-1.3zM576 81v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V81c0-26.5 21.5-48 48-48h480c26.5 0 48 21.5 48 48zM64 220.6c0 76.5 62.1 138.5 138.5 138.5 27.2 0 53.9-8.2 76.5-23.1-72.9-59.3-72.4-171.2 0-230.5-22.6-15-49.3-23.1-76.5-23.1-76.4-.1-138.5 62-138.5 138.2zm224 108.8c70.5-55 70.2-162.2 0-217.5-70.2 55.3-70.5 162.6 0 217.5zm-142.3 76.3c0-8.7-5.7-14.4-14.7-14.7-4.6 0-9.5 1.4-12.8 6.5-2.4-4.1-6.5-6.5-12.2-6.5-3.8 0-7.6 1.4-10.6 5.4V392h-8.2v36.7h8.2c0-18.9-2.5-30.2 9-30.2 10.2 0 8.2 10.2 8.2 30.2h7.9c0-18.3-2.5-30.2 9-30.2 10.2 0 8.2 10 8.2 30.2h8.2v-23zm44.9-13.7h-7.9v4.4c-2.7-3.3-6.5-5.4-11.7-5.4-10.3 0-18.2 8.2-18.2 19.3 0 11.2 7.9 19.3 18.2 19.3 5.2 0 9-1.9 11.7-5.4v4.6h7.9V392zm40.5 25.6c0-15-22.9-8.2-22.9-15.2 0-5.7 11.9-4.8 18.5-1.1l3.3-6.5c-9.4-6.1-30.2-6-30.2 8.2 0 14.3 22.9 8.3 22.9 15 0 6.3-13.5 5.8-20.7.8l-3.5 6.3c11.2 7.6 32.6 6 32.6-7.5zm35.4 9.3l-2.2-6.8c-3.8 2.1-12.2 4.4-12.2-4.1v-16.6h13.1V392h-13.1v-11.2h-8.2V392h-7.6v7.3h7.6V416c0 17.6 17.3 14.4 22.6 10.9zm13.3-13.4h27.5c0-16.2-7.4-22.6-17.4-22.6-10.6 0-18.2 7.9-18.2 19.3 0 20.5 22.6 23.9 33.8 14.2l-3.8-6c-7.8 6.4-19.6 5.8-21.9-4.9zm59.1-21.5c-4.6-2-11.6-1.8-15.2 4.4V392h-8.2v36.7h8.2V408c0-11.6 9.5-10.1 12.8-8.4l2.4-7.6zm10.6 18.3c0-11.4 11.6-15.1 20.7-8.4l3.8-6.5c-11.6-9.1-32.7-4.1-32.7 15 0 19.8 22.4 23.8 32.7 15l-3.8-6.5c-9.2 6.5-20.7 2.6-20.7-8.6zm66.7-18.3H408v4.4c-8.3-11-29.9-4.8-29.9 13.9 0 19.2 22.4 24.7 29.9 13.9v4.6h8.2V392zm33.7 0c-2.4-1.2-11-2.9-15.2 4.4V392h-7.9v36.7h7.9V408c0-11 9-10.3 12.8-8.4l2.4-7.6zm40.3-14.9h-7.9v19.3c-8.2-10.9-29.9-5.1-29.9 13.9 0 19.4 22.5 24.6 29.9 13.9v4.6h7.9v-51.7zm7.6-75.1v4.6h.8V302h1.9v-.8h-4.6v.8h1.9zm6.6 123.8c0-.5 0-1.1-.3-1.6-.3-.3-.5-.8-.8-1.1-.3-.3-.8-.5-1.1-.8-.5 0-1.1-.3-1.6-.3-.3 0-.8.3-1.4.3-.5.3-.8.5-1.1.8-.5.3-.8.8-.8 1.1-.3.5-.3 1.1-.3 1.6 0 .3 0 .8.3 1.4 0 .3.3.8.8 1.1.3.3.5.5 1.1.8.5.3 1.1.3 1.4.3.5 0 1.1 0 1.6-.3.3-.3.8-.5 1.1-.8.3-.3.5-.8.8-1.1.3-.6.3-1.1.3-1.4zm3.2-124.7h-1.4l-1.6 3.5-1.6-3.5h-1.4v5.4h.8v-4.1l1.6 3.5h1.1l1.4-3.5v4.1h1.1v-5.4zm4.4-80.5c0-76.2-62.1-138.3-138.5-138.3-27.2 0-53.9 8.2-76.5 23.1 72.1 59.3 73.2 171.5 0 230.5 22.6 15 49.5 23.1 76.5 23.1 76.4.1 138.5-61.9 138.5-138.4z"></path></svg>
            </div>
            <div>
              <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 576 512" height="5em" width="5em" xmlns="http://www.w3.org/2000/svg"><path d="M470.1 231.3s7.6 37.2 9.3 45H446c3.3-8.9 16-43.5 16-43.5-.2.3 3.3-9.1 5.3-14.9l2.8 13.4zM576 80v352c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h480c26.5 0 48 21.5 48 48zM152.5 331.2L215.7 176h-42.5l-39.3 106-4.3-21.5-14-71.4c-2.3-9.9-9.4-12.7-18.2-13.1H32.7l-.7 3.1c15.8 4 29.9 9.8 42.2 17.1l35.8 135h42.5zm94.4.2L272.1 176h-40.2l-25.1 155.4h40.1zm139.9-50.8c.2-17.7-10.6-31.2-33.7-42.3-14.1-7.1-22.7-11.9-22.7-19.2.2-6.6 7.3-13.4 23.1-13.4 13.1-.3 22.7 2.8 29.9 5.9l3.6 1.7 5.5-33.6c-7.9-3.1-20.5-6.6-36-6.6-39.7 0-67.6 21.2-67.8 51.4-.3 22.3 20 34.7 35.2 42.2 15.5 7.6 20.8 12.6 20.8 19.3-.2 10.4-12.6 15.2-24.1 15.2-16 0-24.6-2.5-37.7-8.3l-5.3-2.5-5.6 34.9c9.4 4.3 26.8 8.1 44.8 8.3 42.2.1 69.7-20.8 70-53zM528 331.4L495.6 176h-31.1c-9.6 0-16.9 2.8-21 12.9l-59.7 142.5H426s6.9-19.2 8.4-23.3H486c1.2 5.5 4.8 23.3 4.8 23.3H528z"></path></svg>
            </div>
          </div> --}}
        </div>
    </div>
      <div class="mt-8">
        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-700" id="checkoutBtn">Proceed to Checkout</a>
      </div>
    </form>
    {{-- <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full whitespace-nowrap">
            <thead>
                <tr class="bg-gray-200">
                    <th class="py-3 px-4 text-left font-semibold text-gray-700">Product</th>
                    <th class="py-3 px-4 text-left font-semibold text-gray-700">Price</th>
                    <th class="py-3 px-4 text-left font-semibold text-gray-700">Quantity</th>
                    <th class="py-3 px-4 text-left font-semibold text-gray-700">Total</th>
                    <th class="py-3 px-4 text-left font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>

                @if (Cart::content()->isEmpty())
                <tr>
                    <td colspan="5" class="text-left p-5">Cart is empty.</td>
                </tr>
                @else
                @foreach (Cart::content() as $item)
                <tr class="border-t border-gray-300">
                    <td class="py-3 px-4">{{ $item->name }}</td>
                    <td class="py-3 px-4">{{ $item->price }} KM</td>
                    <td class="py-3 px-4 flex items-center">
                        <div class="flex items-center">
                            <form action="{{ route('shop.cart.update', $item->rowId) }}" method="POST" class="flex h-full">
                                @csrf
                                <input type="hidden" name="product_qty" value="{{ $item->qty - 1 }}">
                                <button type="submit" class="px-2 text-blue-500 hover:text-blue-700">-</button>
                            </form>
                            <span class="px-2 h-full">{{ $item->qty }}</span>
                            <form action="{{ route('shop.cart.update', $item->rowId) }}" method="POST" class="flex h-full">
                                @csrf
                                
                                <input type="hidden" name="product_qty" value="{{ $item->qty + 1}}">
                                <button type="submit" class="px-2 text-blue-500 hover:text-blue-700">+</button>
                            </form>
                        </div>
                    </td>
                    <td class="py-3 px-4">{{ $item->price * $item->qty }} KM</td>
                    <td class="py-3 px-4">
                        <form action="{{ route('shop.cart.remove', $item->rowId) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">Remove</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-gray-200">
                    <td></td>
                    <td colspan="3" class="py-3 px-1 text-right">Subtotal:</td>
                    <td class="py-3 px-1">{{ Cart::subtotal() }} KM</td>
                </tr>
            </tfoot>
        </table>
    </div> --}}

    {{-- <div class="flex px-4 border-b-[2px] py-2">
      <div class="flex flex-col gap-y-8 w-full">
        <div class="flex justify-between gap-x-8">
          <h1 class="text-2xl text-gray-800 hover:text-gray-600 transition-colors uppercase">{{ $item->name }}</h1>
          <h2 class="text-blue-500 text-xl tracking-wide border-blue-500 whitespace-nowrap">{{ $item->options->price * $item->qty }} KM</h2>
        </div>
        <div class="flex w-full justify-between gap-x-3">
          <img src="{{ asset('storage/images/'.$item->options->url) }}" alt="" class="max-w-[180px] w-full">
          <div class="flex flex-col max-w-[250px] w-full gap-y-4">
            <p class="text-gray-600 text-lg leading-relaxed my-4 text-justify break-words h-[150px] overflow-y-scroll">{{ $item->options->description }}</p>
            <div class="flex justify-between">
              <div class="flex items-center">
                <form action="{{ route('shop.cart.update', $item->rowId) }}" method="POST" class="flex h-full">
                  @csrf
                  <input type="hidden" name="product_qty" value="{{ $item->qty - 1 }}">
                  <button type="submit" class="px-2 text-blue-500 hover:text-blue-700">-</button>
                </form>
                <span class="px-2 h-full">{{ $item->qty }}</span>
                <form action="{{ route('shop.cart.update', $item->rowId) }}" method="POST" class="flex h-full">
                  @csrf
                  <input type="hidden" name="product_qty" value="{{ $item->qty + 1}}">
                  <button type="submit" class="px-2 text-blue-500 hover:text-blue-700">+</button>
                </form>
              </div>
              <form action="{{ route('shop.cart.remove', $item->rowId) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-700">Remove</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div> --}}
