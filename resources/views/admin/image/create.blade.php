@extends('layouts.admin')

@section('title')
    Create
@endsection

@section('content')
    <div class="left-[262px] relative overflow-x-auto sm:rounded-lg p-7 w-full flex flex-col table__width">
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-3xl font-bold mb-4">Create a New Tag</h1>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.image.save') }}" method="POST" class="mt-6 max-w-[200px]">
                @csrf
                <div class="mb-4">
                    <label for="sirina" class="block text-gray-700 font-bold mb-2">Sirina</label>
                    <input type="text" name="sirina" id="sirina" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('name') }}" required>
                </div>
                <div class="mb-4">
                    <label for="duzina" class="block text-gray-700 font-bold mb-2">Duzina</label>
                    <input type="text" name="duzina" id="duzina" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('name') }}" required>
                </div>
                <div class="mb-4">
                    <label for="cijena" class="block text-gray-700 font-bold mb-2">Cijena</label>
                    <input type="text" name="cijena" id="cijena" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('name') }}" required>
                </div>
                <div class="mb-4">
                    <label for="boja-okvira" class="block text-gray-700 font-bold mb-2">Boja Okvira</label>
                    <input type="text" name="boja-okvira" id="boja-okvira" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('name') }}" required>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Kreiraj Dimenzije
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
