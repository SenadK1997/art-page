@extends('layouts.master')

@section("title")

Orders || Foco-art

@endsection

@section('content')
<div class="flex max-w-screen-xl mx-auto w-full gap-x-4 justify-between max-md:flex-col max-md:gap-y-10 relative top-[120px] mb-[125px] h-full max-md:h-full max-md:flex-col-reverse">
    <div class="flex flex-col max-w-[540px] gap-y-5 w-full mx-auto">
        @if (Session::has('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-3 mt-3" role="alert">
                <strong class="font-bold">Uspjesno obavljena transakcija! </strong>
                <span class="block sm:inline">{{ Session::get('success') }}</span>
            </div>
        @endif
        @foreach ($orders->products as $order)
            <div class="flex flex-col max-w-[540px] w-full">
                <div class="flex flex-col justify-center">
                    <div class="relative flex flex-col md:flex-row md:space-x-5 space-y-3 md:space-y-0 rounded-xl shadow-lg p-3 max-w-xs md:max-w-3xl mx-auto border border-white bg-white">
                        <div class="w-full md:w-1/3 bg-white grid place-items-center">
                            <img src="{{ asset('storage/images/'.$order->url) }}" alt="tailwind logo" class="rounded-xl" />
                        </div>
                        <div class="w-full md:w-2/3 bg-white flex flex-col space-y-2 p-3 justify-between">
                            <div class="flex flex-col gap-y-4">
                                <div class="flex justify-between item-center">
                                    <h3 class="font-medium text-gray-800 md:text-3xl text-xl">{{ $order->pivot->itemName }}</h3>
                                </div>
                                <p class="md:text-lg text-gray-500 text-base">{{ $order->description }}</p>
                            </div>
                            <div class="flex justify-between item-center">
                                <div class="flex items-center justify-between w-full">
                                    <p class="text-xl font-medium text-gray-800">
                                        {{ $order->pivot->price * $order->pivot->qty }} KM
                                    </p>
                                    <div class="bg-gray-200 px-3 py-1 gap-x-2 rounded-full text-xs font-medium text-gray-800 inline-flex items-center">
                                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M160 288h-56c-4.42 0-8-3.58-8-8v-16c0-4.42 3.58-8 8-8h56v-64h-56c-4.42 0-8-3.58-8-8v-16c0-4.42 3.58-8 8-8h56V96h-56c-4.42 0-8-3.58-8-8V72c0-4.42 3.58-8 8-8h56V32c0-17.67-14.33-32-32-32H32C14.33 0 0 14.33 0 32v448c0 2.77.91 5.24 1.57 7.8L160 329.38V288zm320 64h-32v56c0 4.42-3.58 8-8 8h-16c-4.42 0-8-3.58-8-8v-56h-64v56c0 4.42-3.58 8-8 8h-16c-4.42 0-8-3.58-8-8v-56h-64v56c0 4.42-3.58 8-8 8h-16c-4.42 0-8-3.58-8-8v-56h-41.37L24.2 510.43c2.56.66 5.04 1.57 7.8 1.57h448c17.67 0 32-14.33 32-32v-96c0-17.67-14.33-32-32-32z"></path>
                                        </svg>
                                        <span>{{ $order->pivot->width }} x {{ $order->pivot->height }} cm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="flex flex-col relative max-w-[540px] w-full rounded-xl gap-y-5 h-fit shadow-xl mx-auto">
        <div class="relative bg-white py-6 px-6 shadow-xl">
            <div class=" text-white flex items-center absolute rounded-full py-4 px-4 shadow-xl bg-green-500 left-4 -top-6">
                <!-- svg  -->
                {{-- <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg> --}}
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 16 16" height="2em" width="2em" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M1.5 1a.5.5 0 00-.5.5v3a.5.5 0 01-1 0v-3A1.5 1.5 0 011.5 0h3a.5.5 0 010 1h-3zM11 .5a.5.5 0 01.5-.5h3A1.5 1.5 0 0116 1.5v3a.5.5 0 01-1 0v-3a.5.5 0 00-.5-.5h-3a.5.5 0 01-.5-.5zM.5 11a.5.5 0 01.5.5v3a.5.5 0 00.5.5h3a.5.5 0 010 1h-3A1.5 1.5 0 010 14.5v-3a.5.5 0 01.5-.5zm15 0a.5.5 0 01.5.5v3a1.5 1.5 0 01-1.5 1.5h-3a.5.5 0 010-1h3a.5.5 0 00.5-.5v-3a.5.5 0 01.5-.5z" clip-rule="evenodd"></path>
                    <path fill-rule="evenodd" d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="mt-8">
                <div class="flex w-full justify-between items-center">
                    <p class="text-xl font-semibold my-2">{{ $orders->fullname }}</p>
                        @if ($orders->status == false)
                                <p class="text-red-700 font-semibold">Nije poslano</p>
                        @else
                            <p class="text-green-700 font-semibold">Poslano</p>
                        @endif
                </div>
                <div class="flex space-x-2 text-gray-400 text-sm">
                    <!-- svg  -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p>{{ $orders->zipcode }}, {{ $orders->address }}</p> 
                </div>
                <div class="flex space-x-2 text-gray-400 text-sm my-3">
                    <!-- svg  -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p>{{ $orders->created_at }}</p> 
                </div>
                <div class="border-t-2 "></div>

                <div class="flex justify-between">
                    <div class="my-2">
                        <p class="font-semibold text-base mb-2">Email:</p>
                        <div class="flex space-x-2">
                            <p class="text-base text-gray-400 font-semibold">{{ $orders->email }}</p>
                        </div>
                    </div>
                    <div class="my-2">
                        <p class="font-semibold text-base mb-2">Ukupna cijena:</p>
                        <div class="text-base text-gray-400 font-semibold">
                            <p>{{ $orders->totalPrice }} BAM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- <div class="flex items-center justify-center h-[500px]">
    <div class="max-w-4xl w-full bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="max-w-screen-xl divide-y divide-gray-200">
            <thead>
              <tr>
                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Naziv artikla
                </th>
                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Sirina
                </th>
                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Duzina
                </th>
                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cijena
                </th>
                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Kolicina
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($orders->products as $order)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $order->title }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $order->pivot->width}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $order->pivot->height }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $order->pivot->price }} BAM
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $order->pivot->qty }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
  </div> --}}