@extends('admin.layout')

@section('content')

<h2>➕ Add New Room</h2>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

@php
$groupedRooms = $rooms->groupBy('category_id');
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    {{-- LEFT: CREATE FORM --}}
    <div class="card">
        <h3>➕ Add New Room</h3>

        <form method="POST" action="/admin/rooms/create">
            @csrf

            <label>Room Number</label>
            <input type="text" name="room_number" placeholder="e.g. 101" required>

            <label>Room Category</label>
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $c)
                    <option value="{{ $c['id'] }}">
                        {{ $c['name'] }} (₱{{ number_format($c['price'], 2) }})
                    </option>
                @endforeach
            </select>

            <label>Image URL (optional)</label>
            <input type="text" name="image_url" placeholder="https://...">

            <button type="submit" class="btn btn-primary" style="width:100%;padding:11px;">
                Save Room
            </button>
        </form>
    </div>

    {{-- RIGHT: EXISTING ROOMS --}}
    <div class="card" style="overflow:auto;max-height:80vh;">
        <h3>🏠 Existing Rooms</h3>

        @foreach($categories as $category)
            <div style="margin-top:15px;">
                <h4 style="background:#0a4a6e;color:white;padding:8px;border-radius:6px;">
                    {{ $category['name'] }}
                </h4>

                @forelse($groupedRooms[$category['id']] ?? [] as $room)
                    <div style="padding:10px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between;">
                        <div>
                            <b>Room {{ $room['room_number'] }}</b>
                            <span style="color:{{ $room['status'] === 'available' ? 'green' : 'orange' }};margin-left:8px;font-size:13px;">
                                {{ ucfirst($room['status']) }}
                            </span>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <a href="/admin/rooms/edit/{{ $room['id'] }}" class="btn btn-primary" style="font-size:12px;padding:5px 8px;">Edit</a>
                            <a href="/admin/rooms/delete/{{ $room['id'] }}"
                               onclick="return confirm('Delete this room?')"
                               class="btn btn-danger" style="font-size:12px;padding:5px 8px;">Del</a>
                        </div>
                    </div>
                @empty
                    <p style="color:#999;padding:8px;font-size:13px;">No rooms in this category.</p>
                @endforelse
            </div>
        @endforeach
    </div>

</div>

@endsection
