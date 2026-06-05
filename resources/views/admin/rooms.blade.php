@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>Rooms Management</h2>
    <a class="btn" href="/admin/rooms/create">+ Add Room</a>
</div>

@if(session('success'))
    <div style="padding:10px; background:#d4edda; color:#155724; border-radius:6px; margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:15px;">

@foreach($rooms as $room)


<div class="card">

    <img src="{{ $room['image_url'] ?? 'https://via.placeholder.com/400' }}"
         style="width:100%; height:180px; object-fit:cover; border-radius:8px;">

    <!-- ROOM NUMBER -->
    <p style="
        background:#0077b6;
        color:white;
        display:inline-block;
        padding:5px 10px;
        border-radius:5px;
        margin-top:10px;
        font-weight:bold;
    ">
        Room No: {{ $room['room_number'] ?? 'N/A' }}
    </p>

    <h3>{{ $room['name'] ?? 'No name' }}</h3>

    @php
    $category = $categories->firstWhere(
        'id',
        $room['category_id'] ?? null
    );
    @endphp

    <p>
    ₱{{ number_format($category['price'] ?? 0, 2) }}
    </p>

    @php
    $category = $categories->firstWhere(
        'id',
        $room['category_id'] ?? null
    );
    @endphp

    <p>{{ $category['description'] ?? 'No description' }}</p>

    <a class="btn btn-danger"
       href="/admin/rooms/delete/{{ $room['id'] }}"
       onclick="return confirm('Delete this room?')">
       Delete
    </a>

   
    <a href="/admin/rooms/calendar/{{ $room['id'] }}" class="btn">
    📅 Calendar
</a>

</div>

@endforeach

</div>

@endsection