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
          <?php Cart::destroy() ?>
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
          <div class="flex flex-col">
            <h2>Nacin placanja:</h2>
              <div class="mt-8">
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-700" id="checkoutBtn">Paypal placanje</a>
              </div>
          </div>
        </div>
      </form>
    @endsection
  @push('script')
  <script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_LIVE_CLIENT_ID') }}"></script>
  @endpush
