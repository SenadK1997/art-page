@extends('layouts.admin')

@section('title')
    Edit
@endsection

@section('content')
<div class="left-[262px] relative overflow-x-auto sm:rounded-lg p-7 w-full flex flex-col table__width h-[100vh] justify-center">
    <form method="POST" action="{{ route('admin.tag.update_tags', $tag->id) }}" class="max-w-lg mx-auto">
        @csrf
        @method('PUT')
      
        <div class="mb-4">
          <label for="name" class="block text-gray-700 font-bold mb-2">Name:</label>
          <input type="text" name="name" value="{{ $tag->name }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
      
        <div class="flex items-center justify-center">
          <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Update
          </button>
        </div>
      </form>
      
</div>
@endsection

