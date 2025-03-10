@extends('layouts.admin')

@section("title")

Orders || Foco-art

@endsection

@section('content')
@if ($orders->isEmpty())
  <div class="relative left-[267px]">
    <h1 class="text-3xl text-blue-700">Nema ordera</h1>
  </div>
@else
<div class="flex relative left-[350px] top-[100px]">
    <!-- New Orders tab -->        
    <div id="newOrders" class="tabcontent" style="display: block;">
        <div class="max-w-3xl mx-auto">
            <div class="flex mb-4">
              <button class="tablink px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md" onclick="openTab('newOrders')">New Orders</button>
                <button class="tablink px-4 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-md" onclick="openTab('completedOrders')">Completed Orders</button>
            </div>
            
            <table class="min-w-full bg-white border border-gray-300">
              <thead>
                <tr>
                  <th class="py-2 px-4 border-b font-medium">Order ID</th>
                  <th class="py-2 px-4 border-b font-medium">Full Name</th>
                  <th class="py-2 px-4 border-b font-medium">Address</th>
                  <th class="py-2 px-4 border-b font-medium">Zip Code</th>
                  <th class="py-2 px-4 border-b font-medium">Country</th>
                  <th class="py-2 px-4 border-b font-medium">Email</th>
                  <th class="py-2 px-4 border-b font-medium">Phone</th>
                  <th class="py-2 px-4 border-b font-medium">Request</th>
                  <th class="py-2 px-4 border-b font-medium">Total Price</th>
                  <th class="py-2 px-4 border-b font-medium">Status</th>
                  <th class="py-2 px-4 border-b font-medium">Action</th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($orders as $order)
                      @if ($order->status == 0)
                          <tr>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap text-center">
                                  <a href="/completed/order/{{$order->id}}" target="_blank">
                                      {{ $order->id }}
                                  </a>
                              </td>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->fullname }}</td>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->address }}</td>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->zipcode }}</td>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->country }}</td>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->email }}</td>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->phone }}</td>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->request }}</td>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->totalPrice }}</td>
                              <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">
                                  @if ($order->status == 0)
                                  Nije poslano
                                  @else
                                  Poslano
                                  @endif
                              </td>
                              <td class="py-2 px-4 border-b">
                                  <button onclick="updateStatus({{$order->id}})" class="px-4 py-2 text-sm text-white bg-blue-500 rounded-md">
                                      @if ($order->status == 0)
                                          Poslano
                                      @else
                                          Vrati u neposlane
                                      @endif
                                  </button>
                                  {{-- <button id="updateButton-{{$order->id}}" class="px-4 py-2 text-sm text-white bg-blue-500 rounded-md" onclick="updateStatus({{$order->id}})">Poslano</button> --}}
                                  {{-- onclick="transferRow(event, 'completedOrders', this)" --}}
                              </td>
                          </tr>
                      @endif
                  @endforeach
            </tbody>
        </table>
    </div>
</div>
<div id="confirmationModal" class="modal fixed inset-0 flex items-center justify-center z-10" style="display: none">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
  
    <div class="modal-container bg-white w-1/2 mx-auto rounded shadow-lg z-50">
      <div class="modal-content p-4">
        <h3 class="text-xl font-bold mb-2">Confirmation</h3>
        <p class="mb-4">Are you sure you want to update the status?</p>
        <div class="flex justify-end">
          <button onclick="confirmUpdateStatus({{$order->id}})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">OK</button>
          <button onclick="closeConfirmationModal()" class="bg-gray-400 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Cancel</button>
        </div>
      </div>
    </div>
  </div>

    <!-- Completed Orders tab -->
    <div id="completedOrders" class="tabcontent">
        <div class="max-w-3xl mx-auto">
            <div class="flex mb-4">
                <button class="tablink px-4 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-md" onclick="openTab('newOrders')">New Orders</button>
                <button class="tablink px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md" onclick="openTab('completedOrders')">Completed Orders</button>
              </div>
            <table class="min-w-full bg-white border border-gray-300">
              <thead>
                <tr>
                  <th class="py-2 px-4 border-b font-medium">Order ID</th>
                  <th class="py-2 px-4 border-b font-medium">Full Name</th>
                  <th class="py-2 px-4 border-b font-medium">Address</th>
                  <th class="py-2 px-4 border-b font-medium">Zip Code</th>
                  <th class="py-2 px-4 border-b font-medium">Country</th>
                  <th class="py-2 px-4 border-b font-medium">Email</th>
                  <th class="py-2 px-4 border-b font-medium">Phone</th>
                  <th class="py-2 px-4 border-b font-medium">Request</th>
                  <th class="py-2 px-4 border-b font-medium">Total Price</th>
                  <th class="py-2 px-4 border-b font-medium">Status</th>
                  <th class="py-2 px-4 border-b font-medium">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($orders as $order)
                    @if ($order->status == 1)
                        <tr>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap text-center">
                                <a href="/completed/order/{{$order->id}}" target="_blank">
                                    {{ $order->id }}
                                </a>
                            </td>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->fullname }}</td>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->address }}</td>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->zipcode }}</td>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->country }}</td>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->email }}</td>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->phone }}</td>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->request }}</td>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">{{ $order->totalPrice }} EUR</td>
                            <td class="py-2 px-4 border-b overflow-x-scroll max-w-[150px] whitespace-nowrap">
                                @if ($order->status == 0)
                                Nije poslano
                                @else
                                Poslano
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b">
                                <button onclick="updateStatus({{$order->id}})" class="px-4 py-2 text-sm text-white bg-blue-500 rounded-md">
                                    Vrati u neposlane
                                </button>
                            </td>
                        </tr>
                    @endif
                @endforeach
                @endif
              </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    function updateStatus(orderId) {
    $.ajax({
        url: '/completed/order/' + orderId, // Replace with the actual URL to handle the update on the server
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: {
            // _method: 'PUT',
            orderId: orderId
        },
        success: function(response) {
            // Handle the success response, e.g., update the UI or display a success message
            // console.log('poslano');
            location.reload();
            // ...
        },
        error: function(xhr, status, error) {
            // Handle the error response, e.g., display an error message
            console.error(error);
        }
    });
}
function showConfirmationModal(orderId) {
  var modal = document.getElementById("confirmationModal");
  modal.style.display = "flex";
}

function closeConfirmationModal() {
  var modal = document.getElementById("confirmationModal");
  modal.style.display = "none";
}

function confirmUpdateStatus(orderId) {
  closeConfirmationModal();
  updateStatus(orderId);
  location.reload();
}
</script>
<script>
    function openTab(tabId) {
      var tabContent = document.getElementById(tabId);
      var tabButtons = document.getElementsByClassName("tablink");
    
      // Hide all tab contents
      var tabContents = document.getElementsByClassName("tabcontent");
      for (var i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = "none";
      }
    
      // Remove 'active' class from all tab buttons
      for (var i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
      }
    
      // Show the selected tab content
      tabContent.style.display = "block";
    
      // Add 'active' class to the clicked tab button
      event.currentTarget.classList.add("active");
    }
    
    // Open the first tab by default
    document.getElementsByClassName("tablink")[0].click();
</script>
@endpush