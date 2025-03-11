@extends('layouts.carts')
@section("title")

Cart || Foco-art

@endsection

@section('content')
<div class="container mx-auto py-6 p-2">
  <div class="mt-4 mb-4 md:hidden">
    <a href="/shop" class="inline-block px-4 py-2 text-white bg-blue-500 hover:bg-blue-700 rounded-md transition-colors duration-300 ease-in-out">
        Nastavi s kupovinom
    </a>
  </div>
  <div class="flex flex-col md:flex-row gap-6 max-w-screen-xl mx-auto w-full">
    @if (Session::has('error'))
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline">{{ Session::get('error') }}</span>
      </div>
    @endif

    <div class="flex flex-col w-full max-w-[540px] gap-y-6">
      @if (Session::has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
          <strong class="font-bold">Success:</strong>
          <span class="block sm:inline">{{ Session::get('success') }}</span>
        </div>
      @endif

      <form action="{{ route('create.order') }}" method="POST" id="create_order" class="flex flex-col border-[2px] w-full p-4 bg-gray-200 rounded-xl gap-y-2 h-fit">
        @csrf
        <div>
          <div>
            <label for="full_name" class="block text-sm font-medium text-gray-700">Ime i Prezime:</label>
            <input type="text" id="full_name" name="full_name" required class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Adresa:</label>
            <input type="text" id="address" name="address" required maxlength="50" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="country" class="block text-sm font-medium text-gray-700">Država:</label>
            <input type="text" id="country" name="country" required class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email (Opcionalno):</label>
            <input type="email" id="email" name="email" class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Telefon:</label>
            <input type="text" id="phone" name="phone" required class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="zipcode" class="block text-sm font-medium text-gray-700">Poštanski broj:</label>
            <input type="text" id="zipcode" name="zipcode" required class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="request" class="block text-sm font-medium text-gray-700">Dodatni zahtjevi (Opcionalno):</label>
            <textarea id="request" name="request" rows="2" maxlength="255" placeholder="Unesite dodatne zahtjeve ovdje..." class="mt-1 w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
          </div>
        </div>
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
          <div class="g-recaptcha" data-sitekey="6Le2n_AqAAAAAOCAQ74hbyvY_srdBx8InA7KE9pO"></div>
          <div class="flex flex-col">
            <div class="mt-4">
              <button type="button" onclick="grecaptcha.execute()" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-700" id="checkoutBtn">Naruči</button>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div class="flex flex-col w-full gap-y-5">
      <div class="mt-4 mb-4 hidden md:block">
        <a href="/shop" class="inline-block px-4 py-2 text-white bg-blue-500 hover:bg-blue-700 rounded-md transition-colors duration-300 ease-in-out">
            Nastavi s kupovinom
        </a>
      </div>
      <div class="pb-3">
        <h1 class="text-3xl font-bold text-gray-800 mt-8">Korpa:</h1>
      </div>

      @if (Cart::content()->isEmpty())
        <div class="p-6 border">
          <h1>Your cart is empty.</h1>
        </div>
      @endif

      @if (session('success'))
        <?php Cart::destroy() ?>
      @endif

      @foreach (Cart::content() as $item)
      <div class="flex flex-col justify-center mb-18 md:mb-0" id="cart_content">
        <div class="relative flex flex-col md:flex-row md:space-x-5 space-y-3 md:space-y-0 rounded-xl shadow-lg p-3 max-w-xs md:max-w-3xl mx-auto border border-white bg-white">
          <div class="w-full md:w-1/3 bg-white grid place-items-center">
            <img src="{{ asset('storage/images/'.$item->options->url) }}" alt="tailwind logo" class="rounded-xl" />
          </div>
          <div class="w-full md:w-2/3 bg-white flex flex-col space-y-2 p-3 gap-y-5">
            <div class="flex justify-between item-center">
              <h3 title="{{ $item->name }}" class="font-black w-64 truncate text-gray-800 md:text-2xl text-xl">{{ $item->name }}</h3>
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
                <span class="text-xl text-gray-600">{{ $item->price }} KM</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
@push('script')
  <script>
    function onSubmit(token) {
      document.getElementById("create_order").submit();
    }
  </script>
@endpush
