@extends('admin.layout')

@section('content')

<h2>Add New Room</h2>

@if(session('success'))
    <div style="padding:10px; background:#d4edda; color:#155724; border-radius:6px; margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:red;color:white;padding:10px;border-radius:5px;margin-bottom:10px;">
        {{ session('error') }}
    </div>
@endif

@php
$groupedRooms = $rooms->groupBy('category_id');
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    {{-- LEFT: CREATE FORM --}}
    <div style="background:#fff;padding:20px;border-radius:12px;">
        <h3>➕ Add New Room</h3>

        <form method="POST" action="/admin/rooms/create">
            @csrf

            <input type="text" name="room_number" placeholder="Room Number"
                   style="width:100%;padding:10px;margin-bottom:10px;">

            <select name="category_id" style="width:100%;padding:10px;margin-bottom:10px;">
                @foreach($categories as $c)
                    <option value="{{ $c['id'] }}">
                        {{ $c['name'] }} (₱{{ $c['price'] }})
                    </option>
                @endforeach
            </select>

            <button style="width:100%;padding:10px;background:#0a4a6e;color:white;">
                Save Room
            </button>
        </form>
    </div>

    {{-- RIGHT: EXISTING ROOMS --}}
    <div style="background:#fff;padding:20px;border-radius:12px;overflow:auto;max-height:80vh;">

        <h3>🏠 Existing Rooms</h3>

        @foreach($categories as $category)

            <div style="margin-top:15px;">
                <h4 style="background:#0a4a6e;color:white;padding:8px;border-radius:6px;">
                    {{ $category['name'] }}
                </h4>

                @foreach($groupedRooms[$category['id']] ?? [] as $room)

                    <div style="padding:8px;border-bottom:1px solid #eee;">
                        <b>Room {{ $room['room_number'] }}</b>
                        <span style="color:gray;">({{ $room['status'] }})</span>

                        <a href="/admin/rooms/edit/{{ $room['id'] }}"
                        style="
                                display:inline-block;
                                margin-top:8px;
                                background:#0a4a6e;
                                color:white;
                                padding:6px 10px;
                                border-radius:6px;
                                text-decoration:none;
                        ">
                            Edit
                        </a>
                        <a href="/admin/rooms/delete/{{ $room['id'] }}"
                        onclick="return confirm('Delete this room?')"
                        style="
                                display:inline-block;
                                margin-top:8px;
                                background:#ef4444;
                                color:white;
                                padding:6px 10px;
                                border-radius:6px;
                                text-decoration:none;
                                margin-left:5px;
                        ">
                            Delete
                        </a>
                    </div>

                @endforeach
            </div>

        @endforeach

    </div>

</div>


@endsection