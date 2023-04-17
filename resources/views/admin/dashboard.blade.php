@extends('layouts.admin')

@section('title')
    Dashboard
@endsection
@section('content')

<div class="flex w-full max-w-screen-xl justify-center items-center h-[100vh] mx-auto ">
    <h1 class="text-[30px]">Welcome: Amer FOCO{{ Auth::user() }}</h1>
</div>
 
    {{-- <table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tags as $tag)
            <tr>
                <td>{{ $tag->name }}</td>
                <td>
                    <a href="{{ route('admin.tag.edit', $tag->id) }}">Edit</a>
                    <form action="{{ route('admin.tag.destroy', $tag->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table> --}}

{{-- <form action="{{ route('admin.tag.store') }}" method="POST">
    @csrf
    <label for="name">Tag Name:</label>
    <input type="text" name="name" id="name">
    <button type="submit">Add Tag</button>
</form> --}}


@endsection