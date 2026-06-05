@extends('admin.layout')

@section('content')

<h2>🛎 Checked-In Guests</h2>

<p>Total: {{ count($bookings ?? []) }}</p>

<div class="card">

@forelse($bookings ?? [] as $b)

    <div style="padding:10px; border-bottom:1px solid #ddd;">
        <h3>{{ $b['full_name'] ?? 'No Name' }}</h3>
        <p>🏨 {{ $b['room_name'] ?? 'No Room' }}</p>
        <p>📅 {{ $b['check_in'] }} → {{ $b['check_out'] }}</p>
        <span style="color:green;">{{ $b['status'] }}</span>
    </div>

@empty

    <p>No checked-in guests found.</p>

@endforelse

</div>

@endsection