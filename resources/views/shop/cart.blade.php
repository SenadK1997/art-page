<!-- resources/views/shop/cart.blade.php -->

@extends('layouts.carts')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-4">My Cart</h1>
    <div class="mt-4 mb-4">
        <a href="/shop" class="inline-block px-4 py-2 text-white bg-blue-500 hover:bg-blue-700 rounded-md transition-colors duration-300 ease-in-out">
            Back to Shop
        </a>
    </div>
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full whitespace-nowrap">
            <thead>
                <tr class="bg-gray-200">
                    <th class="py-3 px-4 text-left font-semibold text-gray-700">Product</th>
                    {{-- <th class="py-3 px-4 text-left font-semibold text-gray-700">Prikaz</th> --}}
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
                    {{-- <td class="py-3 px-4 flex">
                        <img src="{{ $item->options->url }}" alt="" class="object-cover p-3">
                    </td> --}}
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
                    {{-- <td></td> --}}
                    <td colspan="3" class="py-3 px-1 text-right">Subtotal:</td>
                    <td class="py-3 px-1">{{ Cart::subtotal() }} KM</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="mt-8">
        <a href="{{ route('shop.cart.checkout') }}" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-700">Proceed to Checkout</a>
    </div>

