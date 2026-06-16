@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>❌ Cancelled Bookings</h2>
</div>

@if(empty($bookings))
    <p>No cancelled bookings.</p>
@else

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;">

@foreach($bookings as $b)

<div class="card" style="border-left:5px solid #ef4444;">

    <h3>{{ $b['full_name'] ?? '-' }}</h3>
    <p><b>Email:</b> {{ $b['email'] ?? '-' }}</p>
    <p><b>Phone:</b> {{ $b['phone'] ?? '-' }}</p>

    {{-- FIX: was using $b['room_id'] as fallback — replaced with room_name only --}}
    <p><b>Room:</b> {{ $b['room_name'] ?? 'N/A' }}</p>
    <p><b>Check-in:</b> {{ $b['check_in'] ?? '-' }}</p>
    <p><b>Check-out:</b> {{ $b['check_out'] ?? '-' }}</p>

    <p>
        <b>Status:</b>
        <span style="background:#ef4444;color:white;padding:4px 10px;border-radius:5px;">
            Cancelled
        </span>
    </p>

</div>

@endforeach

</div>
@endif

@endsection
