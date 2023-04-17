@extends('layouts.admin')

@section('title')
    Edit
@endsection

@section('content')
<div class="relative overflow-x-auto sm:rounded-lg p-7 w-full flex flex-col table__width h-[100vh]">
  <form method="POST" action="{{ route('admin.product.update', $product->id) }}" class="max-w-xl w-full mx-auto">
      @csrf
      @method('PUT')
    <div class="flex gap-x-16 max-w-screen-xl">
      <div class="flex flex-col gap-y-3 max-w-[300px]">
        <div class="flex border-stone-900 border-solid border-4 w-fit">
          <img src="{{ $product->url }}" alt="" class="flex object-cover p-3 w-fit h-fit">
        </div>
        <div class="mb-4 max-w-[300px]">
          <label for="url" class="block text-gray-700 font-bold mb-2">URL:</label>
          <input type="text" name="url" value="{{ $product->url }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="flex gap-x-1 flex-row flex-wrap min-w-[400px]">
          @foreach ($product->tags as $item)
              <div class="parent-item flex items-center gap-x-2 bg-gray-700 hover:bg-gray-500 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                <p class="text-white">
                  {{ $item->name }}
                </p>
                  <a href="{{ route('admin.update.delete', [$product->id, $item->pivot->tags_id]) }}" 
                    class="delete-tag text-white" data-product_id="{{ $product->id }}" data-name="{{ $item->pivot->tags_id }}">
                    X
                  </a>
              </div>
          @endforeach
        </div>
      </div>
      <div class="flex flex-col w-full min-w-[340px] max-w-[350px]">
        <div class="mb-4">
          <label for="title" class="block text-gray-700 font-bold mb-2">Title:</label>
          <input type="text" name="title" value="{{ $product->title }}" class="flex w-full shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
      
        <div class="mb-4">
          <label for="description" class="block text-gray-700 font-bold mb-2">Description:</label>
          <textarea name="description" class="shadow appearance-none border rounded w-full py-2 h-[80px] px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ $product->description }}</textarea>
        </div>
        <div class="flex flex-col">
          <div class="flex items-center w-full justify-between">
            <div class="mb-4">
              <label for="amount" class="block text-gray-700 font-bold mb-2">Amount:</label>
              <input type="number" name="amount" value="{{ $product->amount }}" class="max-w-[80px] shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            {{-- Tagovi --}}
              <div class="flex items-center mt-4">
                <div class="flex items-center justify-center">
                  <select id="tagSelect" class="flex min-w-[150px] max-w-[150px] text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 rounded-lg shadow text-sm px-4 py-2.5 focus:ring-4 focus:outline-none focus:ring-blue-300">
                    <option value="">Tagovi</option>
                    @foreach ($tags as $tag)
                      <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="mb-4">
                <label for="price" class="block text-gray-700 font-bold mb-2">Price (BAM):</label>
                <input type="number" name="price" value="{{ $product->price }}" class="max-w-[100px] shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
              </div>
          </div>
              <div id="selectedTagsContainer" class="flex flex-row flex-wrap gap-x-2 gap-y-2 overflow-scroll">
                <input type="hidden" name="selected_tags" id="selectedTagsInput" class="flex bg-red-700">
              </div>
        </div>
        <div class="flex items-center justify-center mt-3">
          <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Update
          </button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
@push('scripts')
  <script>
    var tagSelect = document.getElementById("tagSelect");
    var selectedTagsContainer = document.getElementById("selectedTagsContainer");
    var selectedTagsInput = document.getElementById("selectedTagsInput");
    var selectedTags = [];

    tagSelect.addEventListener("change", function() {
      var selectedOption = this.options[this.selectedIndex];
      var tagId = selectedOption.value;
      var tagName = selectedOption.textContent;

      // If tag is not already selected, add it to the container and the selectedTags array
      if (!selectedTags.includes(tagId)) {
        var selectedTagElement = document.createElement("div");
        selectedTagElement.textContent = tagName;
        selectedTagElement.classList.add('tag');
        selectedTagElement.dataset.id = tagId;
        selectedTagsContainer.appendChild(selectedTagElement);
        selectedTags.push(tagId);
        
        // Remove the selected tag from the tagSelect dropdown
        selectedOption.remove();
      }

      // Update the value of the hidden input field
      selectedTagsInput.value = selectedTags;
    });

    selectedTagsContainer.addEventListener("click", function(event) {
      if (event.target.classList.contains("tag")) {
        var tagId = event.target.dataset.id;
        var tagOption = document.createElement("option");
        tagOption.value = tagId;
        tagOption.textContent = event.target.textContent;
        tagSelect.add(tagOption);
        event.target.remove();
        selectedTags = selectedTags.filter(function(tag) {
          return tag !== tagId;
        });
        selectedTagsInput.value = selectedTags;
      }
    });
  </script>
  <script>
    $(document).ready(function () {
        // Add click event listener to delete buttons
        $('.delete-tag').click(function (event) {
          event.preventDefault(); // prevent anchor from default GET request
            // Get the ID of the tag to be deleted
            var productId = $(this).data('product_id');
            var name = $(this).data('name');
            // var delete_url = $(this).data('href');
            // console.log(delete_url);
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            // Send an AJAX request to delete the product
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
              });
              console.log('tetst');
              $.ajax({
                url: '/admin/update/delete/' + productId + '/' + encodeURIComponent(name),
                data: '_method=DELETE',
                type: 'DELETE',
                success: function (data) {
                    // Update the view to remove the deleted product
                    $('a[data-name="' + name + '"]').closest('.parent-item').remove();
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

