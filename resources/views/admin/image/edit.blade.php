@extends('layouts.admin')

@section('title')
    Edit
@endsection

@section('content')
<div class="md:left-[262px] relative overflow-x-auto sm:rounded-lg p-7 min-w-full md:max-w-screen-lg md:min-w-max flex flex-col table__width h-[100vh] justify-center">
    <form method="POST" action="{{ route('admin.image.edit_images', $img->id) }}" class="max-w-lg mx-auto">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="sirina" class="block text-gray-700 font-bold mb-2">Sirina</label>
            <input type="text" value="{{ $img->width }}" name="sirina" id="sirina" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('name') }}" required>
        </div>
        <div class="mb-4">
            <label for="duzina" class="block text-gray-700 font-bold mb-2">Duzina</label>
            <input type="text" value="{{ $img->height }}" name="duzina" id="duzina" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('name') }}" required>
        </div>
        <div class="mb-4">
            <label for="cijena" class="block text-gray-700 font-bold mb-2">Cijena</label>
            <input type="text" value="{{ $img->price }}" name="cijena" id="cijena" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('name') }}" required>
        </div>
        <div class="mb-4">
            <label for="boja-okvira" class="block text-gray-700 font-bold mb-2">Boja Okvira</label>
            <input type="text" value="{{ $img->color }}" name="boja-okvira" id="boja-okvira" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('name') }}" required>
        </div>
      
        <div class="flex items-center justify-center">
          <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Update
          </button>
        </div>
      </form>
</div>
@endsection

