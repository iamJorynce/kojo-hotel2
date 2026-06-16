@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>🏠 Rooms Management</h2>
    <a class="btn" href="/admin/rooms/create">+ Add Room</a>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:15px;">

@foreach($rooms as $room)

@php
// FIX: removed duplicate $category lookup — only do it once per room
$category = $categories->firstWhere('id', $room['category_id'] ?? null);
@endphp

<div class="card">


    <p style="
        background:#0077b6;color:white;
        display:inline-block;padding:5px 10px;
        border-radius:5px;margin-top:10px;font-weight:bold;">
        Room No: {{ $room['room_number'] ?? 'N/A' }}
    </p>

    <h3 style="margin:8px 0 4px;">{{ $room['name'] ?? 'No name' }}</h3>

    <p style="color:#0a4a6e;font-weight:bold;">
        ₱{{ number_format($category['price'] ?? 0, 2) }}
    </p>

    <p style="color:#666;font-size:13px;">
        {{ $category['description'] ?? 'No description' }}
    </p>

    <p>
        <b>Status:</b>
        <span style="
            background: {{ $room['status'] === 'available' ? 'green' : ($room['status'] === 'occupied' ? 'red' : 'orange') }};
            color:white;padding:3px 8px;border-radius:5px;font-size:12px;">
            {{ ucfirst($room['status'] ?? 'unknown') }}
        </span>
    </p>

    <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;">
       
        <a href="/admin/rooms/calendar/{{ $room['id'] }}" class="btn">📅 Calendar</a>
        
    </div>

</div>

@endforeach

</div>

@endsection
