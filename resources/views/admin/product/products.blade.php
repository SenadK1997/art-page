@extends('layouts.admin')

@section('title')
    Products
@endsection
@section('content')
    
<div class="md:left-[267px] relative overflow-x-auto sm:rounded-lg p-7 min-w-full md:max-w-screen-lg md:min-w-max flex flex-col table__width sm:max-w-md">
    <div class="flex justify-center">
        <div class="relative flex flex-col items-center justify-center w-full p-1 pb-5">
            <h1 class="flex justify-center text-2xl font-medium text-stone-100 dark:text-black">LISTA SLIKA<h1>
            <a href="{{ route('admin.product.create') }}" class="flex items-center gap-x-2 font-medium text-green-600 dark:text-blue-500 hover:underline" type="button">
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1" viewBox="0 0 48 48" enable-background="new 0 0 48 48" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><circle fill="#4CAF50" cx="24" cy="24" r="21"></circle><g fill="#fff"><rect x="21" y="14" width="6" height="20"></rect><rect x="14" y="21" width="20" height="6"></rect></g></svg>
                Dodaj
            </a>
        </div>
    </div>
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 mx-auto relative border">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 justify-between w-full max-w-screen-xl">
            <tr>
                <th scope="col" class="max-w-20 md:max-w-32 px-6 py-3">Naslov</th>
                <th scope="col" class="hidden md:table-cell px-6 py-3">Opis</th>
                <th scope="col" class="hidden md:table-cell px-6 py-3">Kolicina</th>
                <th scope="col" class="max-w-20 px-6 py-3 text-center">Slika</th>
                <th scope="col" class="max-w-20 px-6 py-3 gap-x-2 justify-center items-center">Cijena</th>
                <th scope="col" class="px-6 py-3">Akcije</th>
            </tr>
        </thead>
        <tbody>
            @if ($products->isEmpty())
            <tr>
                <td colspan="2" class="px-6 py-4 text-gray-900 dark:text-white">
                    No products available.
                </td>
            </tr>
            @endif
            @foreach ($products as $product)
            <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700">
                <td title="{{ $product->title }}" scope="row" class="max-w-20 md:max-w-32 px-4 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white overflow-clip">{{ $product->title }}</td>
                <td class="hidden md:table-cell px-6 py-4 overflow-hidden" title="{{$product->description}}">{{ $product->description }}</td>
                <td class="hidden md:table-cell px-6 py-4">{{ $product->amount }}</td>
                <td class="max-w-20 px-6 py-4 columns-3xs overflow-scroll">
                    @if ($product->url)
                        <img src="{{ asset('storage/images/'.$product->url) }}" alt="Product Image" class="flex max-w-[100px] max-h-[70px] mx-auto">
                    @endif
                </td>                
                <td class="max-w-20 px-6 py-4 text-center">{{ $product->images->first()->price ?? 'N/A' }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('admin.product.edit', $product->id) }}" class="flex items-center gap-x-2 font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 1024 1024" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path d="M257.7 752c2 0 4-.2 6-.5L431.9 722c2-.4 3.9-1.3 5.3-2.8l423.9-423.9a9.96 9.96 0 0 0 0-14.1L694.9 114.9c-1.9-1.9-4.4-2.9-7.1-2.9s-5.2 1-7.1 2.9L256.8 538.8c-1.5 1.5-2.4 3.3-2.8 5.3l-29.5 168.2a33.5 33.5 0 0 0 9.4 29.8c6.6 6.4 14.9 9.9 23.8 9.9zm67.4-174.4L687.8 215l73.3 73.3-362.7 362.6-88.9 15.7 15.6-89zM880 836H144c-17.7 0-32 14.3-32 32v36c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-36c0-17.7-14.3-32-32-32z"></path></svg>
                        Edit
                    </a>
                    <form class="delete-form" action="{{ route('admin.product.delete', $product->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="flex items-center gap-x-2 delete-btn text-red-600" data-id="{{ $product->id }}">
                            <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 1024 1024" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path d="M360 184h-8c4.4 0 8-3.6 8-8v8h304v-8c0 4.4 3.6 8 8 8h-8v72h72v-80c0-35.3-28.7-64-64-64H352c-35.3 0-64 28.7-64 64v80h72v-72zm504 72H160c-17.7 0-32 14.3-32 32v32c0 4.4 3.6 8 8 8h60.4l24.7 523c1.6 34.1 29.8 61 63.9 61h454c34.2 0 62.3-26.8 63.9-61l24.7-523H888c4.4 0 8-3.6 8-8v-32c0-17.7-14.3-32-32-32zM731.3 840H292.7l-24.2-512h487l-24.2 512z"></path></svg>
                            Delete
                        </button>
                    </form>                    
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('scripts')    
<script>
    // Delete button functionality
    $(document).ready(function () {
        // Add click event listener to delete buttons
        $('.delete-btn').click(function () {
            // Get the ID of the product to be deleted
            var id = $(this).data('id');

            // Send an AJAX request to delete the product
            $.ajax({
                url: '/admin/product/delete/' + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (data) {
                    // Update the view to remove the deleted product
                    $('tr[data-id="' + id + '"]').remove();
                    if (data.reload) {
                        location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    // Handle the error if the request fails
                    console.log(xhr.responseText);
                }
            });
        });
    });
    
</script>
@endpush