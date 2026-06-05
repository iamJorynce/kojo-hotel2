@extends('admin.layout')

@section('content')

<h2>Edit Room</h2>

@if(session('error'))
    <div style="background:red;color:white;padding:10px;border-radius:6px;margin-bottom:15px;">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div style="background:green;color:white;padding:10px;border-radius:6px;margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

<div class="card">

<form method="POST" action="/admin/rooms/update/{{ $room['id'] }}">
@csrf

<label>Room Number</label>
<input name="room_number" value="{{ $room['room_number'] ?? '' }}" required>

<label>Room Name</label>
<select name="category_id">
    @foreach($categories as $c)
        <option value="{{ $c['id'] }}"
            {{ $room['category_id'] == $c['id'] ? 'selected' : '' }}>
            {{ $c['name'] }}
        </option>
    @endforeach
</select>

<label>Image URL</label>
<input name="image_url" value="{{ $room['image_url'] ?? '' }}">

<label>Status</label>
<select name="status">
    <option value="available" {{ ($room['status'] ?? '') == 'available' ? 'selected' : '' }}>Available</option>
    <option value="occupied" {{ ($room['status'] ?? '') == 'occupied' ? 'selected' : '' }}>Occupied</option>
    <option value="maintenance" {{ ($room['status'] ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
</select>

<br><br>

<button class="btn" type="submit">
    Update Room
</button>

</form>

</div>

@endsection