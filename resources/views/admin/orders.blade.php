@extends('layouts.admin')

@section("title")

Orders || Foco-art

@endsection

@section('content')
    <!-- HTML structure for the tabs -->
<div>
    <ul class="tab">
        <li><a href="#" class="tablinks active" onclick="openTab(event, 'newOrders')">New Orders</a></li>
        <li><a href="#" class="tablinks" onclick="openTab(event, 'completedOrders')">Completed Orders</a></li>
    </ul>
    
    <!-- New Orders tab -->
    <div id="newOrders" class="tabcontent" style="display: block;">
        <!-- Form for creating new orders -->
        {{-- <form action="{{ route('orders.create') }}" method="POST"> --}}
            {{-- @csrf --}}
            <!-- Add fields for title, image upload, price, dimensions, address, city, and frame color -->
            <!-- Include a submit button -->
        {{-- </form> --}}
    </div>

    <!-- Completed Orders tab -->
    <div id="completedOrders" class="tabcontent">
        <!-- Iterate over completed orders and display order details -->
        {{-- @foreach ($completedOrders as $order) --}}
            <div class="order">
                <!-- Display order details: title, image, price, dimensions, address, city, and frame color -->
            </div>
        {{-- @endforeach --}}
    </div>
</div>



</section>
@endsection
@push('script')
<!-- JavaScript code to handle tab functionality -->
<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>
@endpush