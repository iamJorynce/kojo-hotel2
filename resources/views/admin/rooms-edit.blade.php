@extends('admin.layout')

@section('content')

<div style="max-width:600px;margin:auto;">

    <h2>✏️ Edit Room</h2>

    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <form method="POST" action="/admin/rooms/update/{{ $room['id'] }}">
            @csrf

            <label>Room Number</label>
            <input name="room_number" value="{{ $room['room_number'] ?? '' }}" required>

            <label>Room Category</label>
            <select name="category_id">
                @foreach($categories as $c)
                    <option value="{{ $c['id'] }}"
                        {{ ($room['category_id'] ?? '') == $c['id'] ? 'selected' : '' }}>
                        {{ $c['name'] }}
                    </option>
                @endforeach
            </select>

            <label>Image URL</label>
            <input name="image_url" value="{{ $room['image_url'] ?? '' }}" placeholder="https://...">

            <label>Status</label>
            <select name="status">
                <option value="available"    {{ ($room['status'] ?? '') == 'available'    ? 'selected' : '' }}>Available</option>
                <option value="occupied"     {{ ($room['status'] ?? '') == 'occupied'     ? 'selected' : '' }}>Occupied</option>
                <option value="reserved"     {{ ($room['status'] ?? '') == 'reserved'     ? 'selected' : '' }}>Reserved</option>
                <option value="maintenance"  {{ ($room['status'] ?? '') == 'maintenance'  ? 'selected' : '' }}>Maintenance</option>
            </select>

            <div style="display:flex;gap:10px;margin-top:5px;">
                <button type="submit" class="btn btn-success" style="flex:1;padding:11px;">
                    ✅ Update Room
                </button>
                <a href="/admin/rooms" class="btn" style="flex:1;padding:11px;text-align:center;">
                    Cancel
                </a>
            </div>

        </form>
    </div>

</div>

@endsection
