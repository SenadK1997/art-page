@extends('layouts.admin')

@section('title')
    Edit
@endsection

@section('content')
<div class="relative overflow-x-auto sm:rounded-lg p-7 min-w-full md:flex md:flex-col table__width h-[100vh]">
  <form method="POST" action="{{ route('admin.product.update', $product->id) }}" class="max-w-xl w-full mx-auto" enctype="multipart/form-data" id="product-update">
      @csrf
      @method('PUT')
    <div class="flex flex-col mb-12 md:mb-0 md:flex md:flex-row md:gap-x-16 md:max-w-screen-lg">
      <div class="flex flex-col gap-y-3 max-w-[300px]">
          @if (session('success'))
              <div class="alert alert-success">
                  {{ session('success') }}
              </div>
          @endif
        <div class="flex border-stone-900 border-solid border-4 w-fit">
          <img src="{{ asset('storage/images/'.$product->url) }}" alt="" class="flex object-cover p-3 w-fit h-fit">
        </div>
        <div class="mb-4 max-w-[300px]">
          <label for="image" class="block text-gray-700 font-bold mb-2">Change Image:</label>
          <input type="file" name="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        {{-- Current Tags --}}
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
          <input type="text" name="title" value="{{ $product->title }}" class="flex w-full shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" maxlength="25">
        </div>
      
        <div class="mb-4">
          <label for="description" class="block text-gray-700 font-bold mb-2">Description:</label>
          <textarea name="description" class="shadow appearance-none border rounded w-full py-2 h-[80px] px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" maxlength="255">{{ $product->description }}</textarea>
        </div>
        
        <div class="flex flex-col">
          <div class="flex items-center w-full justify-between">
            <div class="mb-4">
              <label for="amount" class="block text-gray-700 font-bold mb-2">Amount:</label>
              <input type="number" name="amount" value="{{ $product->amount }}" class="max-w-[80px] shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" max="100">
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
          </div>
              <div id="selectedTagsContainer" class="flex flex-row flex-wrap gap-x-2 gap-y-2 overflow-scroll">
                <input type="hidden" name="selected_tags" id="selectedTagsInput" class="flex bg-red-700">
              </div>
              <div id="selectedImagesContainer" class="flex flex-row flex-wrap gap-x-2 gap-y-2 overflow-scroll">
                <input type="hidden" name="selected_images" id="selectedImagesInput" class="flex bg-red-700">
              </div>
        </div>
        <div class="flex items-center justify-center mt-3">
          <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" form="product-update">
            Update
          </button>
        </div>
      </div>
    </div>
  
    {{-- Add Dimension --}}
    <div class="flex flex-col relative gap-y-4">
      {{-- Dodaj Dimenzije Slika --}}
        {{-- <div class="flex">
          <p type="text" id="imagePrice" class="max-w-[150px] shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" max="100000">{{ $product->images->first()->price }} KM</p>
          <input type="hidden" id="imagePrice" name="price" value="" class="max-w-[100px] shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" max="100000">
        </div> --}}

        <div class="flex items-center gap-x-2" id="addNewDimensions">
          <h1 class="text-2xl font-bold text-gray-800">Dodaj dimenziju</h1>
          <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="2em" width="2em" xmlns="http://www.w3.org/2000/svg"><path fill="#00FF00" stroke="#000" stroke-width="2" d="M12,22 C17.5228475,22 22,17.5228475 22,12 C22,6.4771525 17.5228475,2 12,2 C6.4771525,2 2,6.4771525 2,12 C2,17.5228475 6.4771525,22 12,22 Z M12,18 L12,6 M6,12 L18,12"></path></svg>
        </div>
        <p id="price" class="font-semibold text-xs text-green-500"></p>
        {{-- <form action="{{ route('admin.image.save', $product->id) }}" method="POST" class="mt-6 max-w-[600px] gap-x-4 flex"> --}}
        <div class="mt-6 max-w-[600px] gap-x-4 flex" id="addDimensionBlock">
          <div class="mb-4">
              <label for="sirina" class="block text-gray-700 font-bold mb-2">Sirina</label>
              <input type="text" name="sirina" id="sirina" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="mb-4">
              <label for="duzina" class="block text-gray-700 font-bold mb-2">Duzina</label>
              <input type="text" name="duzina" id="duzina" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="mb-4">
              <label for="cijena" class="block text-gray-700 font-bold mb-2">Cijena</label>
              <input type="text" name="cijena" id="cijena" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="flex items-center justify-between">
              <button class="flex items-center justify-center text-white font-bold" id="add__Dimension">
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="2em" width="2em" xmlns="http://www.w3.org/2000/svg"><path fill="#fff" stroke="#00C356" stroke-width="2" d="M12,22 C17.5228475,22 22,17.5228475 22,12 C22,6.4771525 17.5228475,2 12,2 C6.4771525,2 2,6.4771525 2,12 C2,17.5228475 6.4771525,22 12,22 Z M12,18 L12,6 M6,12 L18,12"></path></svg>
              </button>
          </div>
        </div>
      {{-- </form> --}}


      <div class="max-w-[600px] gap-x-4 flex hidden" id="shownDimensions">
        <div class="mb-4">
            <label for="sirina" class="block text-gray-700 font-bold mb-2">Sirina</label>
            <input type="number" value="" id="shownWidth" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="">
        </div>
        <div class="mb-4">
            <label for="duzina" class="block text-gray-700 font-bold mb-2">Duzina</label>
            <input type="number" value="" id="shownHeight" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="">
        </div>
        <div class="mb-4">
            <label for="cijena" class="block text-gray-700 font-bold mb-2">Cijena</label>
            <input type="number" value="" id="shownPrice" class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="">
        </div>
        <div class="flex items-center justify-center">
          <button type="submit" class="flex items-center justify-center text-white font-bold">
            <svg stroke="currentColor" fill="#064CD6" stroke-width="0" viewBox="0 0 1024 1024" height="2em" width="2em" xmlns="http://www.w3.org/2000/svg"><path d="M257.7 752c2 0 4-.2 6-.5L431.9 722c2-.4 3.9-1.3 5.3-2.8l423.9-423.9a9.96 9.96 0 0 0 0-14.1L694.9 114.9c-1.9-1.9-4.4-2.9-7.1-2.9s-5.2 1-7.1 2.9L256.8 538.8c-1.5 1.5-2.4 3.3-2.8 5.3l-29.5 168.2a33.5 33.5 0 0 0 9.4 29.8c6.6 6.4 14.9 9.9 23.8 9.9zm67.4-174.4L687.8 215l73.3 73.3-362.7 362.6-88.9 15.7 15.6-89zM880 836H144c-17.7 0-32 14.3-32 32v36c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-36c0-17.7-14.3-32-32-32z"></path></svg>
            <svg class="hidden" stroke="currentColor" fill="#00C356" stroke-width="0" viewBox="0 0 448 512" height="2em" width="2em" xmlns="http://www.w3.org/2000/svg"><path d="M433.941 129.941l-83.882-83.882A48 48 0 0 0 316.118 32H48C21.49 32 0 53.49 0 80v352c0 26.51 21.49 48 48 48h352c26.51 0 48-21.49 48-48V163.882a48 48 0 0 0-14.059-33.941zM272 80v80H144V80h128zm122 352H54a6 6 0 0 1-6-6V86a6 6 0 0 1 6-6h42v104c0 13.255 10.745 24 24 24h176c13.255 0 24-10.745 24-24V83.882l78.243 78.243a6 6 0 0 1 1.757 4.243V426a6 6 0 0 1-6 6zM224 232c-48.523 0-88 39.477-88 88s39.477 88 88 88 88-39.477 88-88-39.477-88-88-88zm0 128c-22.056 0-40-17.944-40-40s17.944-40 40-40 40 17.944 40 40-17.944 40-40 40z"></path></svg>
          </button>
        </div>
      </div>
      <div id="clonedShownDimension"></div>
    </div>
  </form>
  {{-- Postojece Dimenzije za editovanje i brisanje --}}
  <div class="flex flex-col relative md:left-[267px] ">
    <h1 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-x-2">
      Postojeće dimenzije
      <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><g><path fill="none" d="M0 0h24v24H0z"></path><path d="M19 22H5a3 3 0 0 1-3-3V3a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v12h4v4a3 3 0 0 1-3 3zm-1-5v2a1 1 0 0 0 2 0v-2h-2zm-2 3V4H4v15a1 1 0 0 0 1 1h11zM6 7h8v2H6V7zm0 4h8v2H6v-2zm0 4h5v2H6v-2z"></path></g></svg>
    </h1>
    <div class="max-w-[600px] w-full gap-x-4 flex flex-wrap">
      @if($product->images->isEmpty())
        <h1 class="text-2xl text-center text-gray-500">Nema postojecih dimenzija. Trebas dodati najmanje jednu.</h1>
      @endif
      @if (Session::has('error'))
          {{ Session::get('error')}}
      @endif
      @foreach ($product->images as $item)
      <form action="{{ route('admin.image.edit_images', $item->id) }}" method="POST">
        @method('PUT')
        @csrf
        <div class="flex min-w-fit w-full gap-x-2 items-center">
          <div class="m-y-2">
            <label for="sirina" class="block text-gray-700 font-bold mb-2">Sirina</label>
            <input type="text" name="sirina" id="sirina" value="{{ $item->width }}" class="appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="m-2">
            <label for="duzina" class="block text-gray-700 font-bold mb-2">Duzina</label>
            <input type="text" name="duzina" id="duzina" value="{{ $item->height }}" class="appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="m-2">
            <label for="cijena" class="block text-gray-700 font-bold mb-2">Cijena</label>
            <input type="text" name="cijena" id="cijena" value="{{ $item->price }}" class="appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="flex flex-col justify-center mt-6">
            <button type="submit" class="flex items-center justify-end gap-x-2 font-medium text-blue-600 dark:text-blue-500 hover:underline">
              <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 1024 1024" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path d="M257.7 752c2 0 4-.2 6-.5L431.9 722c2-.4 3.9-1.3 5.3-2.8l423.9-423.9a9.96 9.96 0 0 0 0-14.1L694.9 114.9c-1.9-1.9-4.4-2.9-7.1-2.9s-5.2 1-7.1 2.9L256.8 538.8c-1.5 1.5-2.4 3.3-2.8 5.3l-29.5 168.2a33.5 33.5 0 0 0 9.4 29.8c6.6 6.4 14.9 9.9 23.8 9.9zm67.4-174.4L687.8 215l73.3 73.3-362.7 362.6-88.9 15.7 15.6-89zM880 836H144c-17.7 0-32 14.3-32 32v36c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-36c0-17.7-14.3-32-32-32z"></path></svg>
              Uredi
            </button>
          </form>
          <form class="delete-form flex justify-end" action="{{ route('admin.image.delete', $item->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="button" class="flex items-center gap-x-2 delete-btn text-red-600" data-id="{{ $item->id }}">
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 1024 1024" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path d="M360 184h-8c4.4 0 8-3.6 8-8v8h304v-8c0 4.4 3.6 8 8 8h-8v72h72v-80c0-35.3-28.7-64-64-64H352c-35.3 0-64 28.7-64 64v80h72v-72zm504 72H160c-17.7 0-32 14.3-32 32v32c0 4.4 3.6 8 8 8h60.4l24.7 523c1.6 34.1 29.8 61 63.9 61h454c34.2 0 62.3-26.8 63.9-61l24.7-523H888c4.4 0 8-3.6 8-8v-32c0-17.7-14.3-32-32-32zM731.3 840H292.7l-24.2-512h487l-24.2 512z"></path></svg>
                Delete
            </button>
          </form>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
  $(document).ready(function() {
    $('#sirina, #duzina').on('change', function() {
        var width = $('#sirina').val();
        var height = $('#duzina').val();

        if (width && height) {
            var price5 = Math.round((width * height) / 100 * 5);
            var price10 = Math.round((width * height) / 100 * 10);
            var price15 = Math.round((width * height) / 100 * 15);

            $('#price').html(
                'Cijena (Apstrakcija): ' + price5 + ' BAM<br>' +
                'Cijena (Realizam): ' + price10 + ' BAM<br>' +
                'Cijena (Panorama): ' + price15 + ' BAM'
            );
        } else {
            $('#price').html('');
        }
    });
});
</script>
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
  {{-- delete images Dimension --}}
  <script>
    $(document).ready(function () {
        // Add click event listener to delete buttons
        $('.delete-images').click(function (event) {
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
  <script>
    $(document).ready(function() {
      $('#add__Dimension').click(function(event) {
        event.preventDefault();
        const sirinaValue = $('#sirina').val();
        const duzinaValue = $('#duzina').val();
        const cijenaValue = $('#cijena').val();
        let $shownDimensions = $('#shownDimensions').clone();
        // let $shownDimensions = $('#shownDimensions').clone().removeClass('hidden').attr('id', 'copiedShownDimensions');
        $shownDimensions.removeClass("hidden");
        $shownDimensions.attr("id", "copiedShownDimensions");
        $('#clonedShownDimension').append($shownDimensions);

        // updatuj na frontendu
        $('#copiedShownDimensions #shownWidth').attr('name', 'shownWidth[]');
        $('#copiedShownDimensions #shownHeight').attr('name', 'shownHeight[]');
        $('#copiedShownDimensions #shownPrice').attr('name', 'shownPrice[]');
        $('#copiedShownDimensions #shownWidth').attr('value', sirinaValue);
        $('#copiedShownDimensions #shownHeight').attr('value', duzinaValue);
        $('#copiedShownDimensions #shownPrice').attr('value', cijenaValue);

        $('#shownWidth').val(sirinaValue);
        $('#shownHeight').val(duzinaValue);
        $('#shownPrice').val(cijenaValue);

        $('#sirina').val('');
        $('#duzina').val('');
        $('#cijena').val('');
      });
    });
    $(document).ready(function() {
      $('#addNewDimensions').click(function(e) {
        e.preventDefault();
        // $('#addDimensionBlock').toggleClass('hidden');
      });
    });
  </script>
<script>
    // Function to handle tag deletion
    function deleteImage(imageId) {
        // Send an AJAX request to the delete route
        $.ajax({
            url: '/admin/image/delete/' + imageId,
            type: 'DELETE',
            data: {
                '_token': '{{ csrf_token() }}',
                'imageId': imageId
            },
            success: function (response) {
                // Reload the page after successful deletion
                location.reload();
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    $(document).on('click', '.delete-btn', function () {
        var imageId = $(this).data('id');
        // Call the deleteTag function with the delteId
        deleteImage(imageId);
    });
</script>
@endpush