@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>🔴 Check-Out Guests</h2>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(empty($bookings) || count($bookings) === 0)
    <p>No checked-in guests.</p>
@else

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;">

@foreach($bookings as $b)

<div class="card" style="border-left:5px solid #3b82f6;">

    <h3>{{ $b['full_name'] ?? 'No Name' }}</h3>
    <p><b>Phone:</b> {{ $b['phone'] ?? '-' }}</p>
    <p><b>Email:</b> {{ $b['email'] ?? '-' }}</p>
    <p><b>Room:</b> {{ $b['room_name'] ?? '-' }} (Room No: {{ $b['room_number'] ?? 'N/A' }})</p>
    <p><b>Check-in:</b> {{ $b['check_in'] }}</p>
    <p><b>Check-out:</b> {{ $b['check_out'] }}</p>
    <p><b>Price/night:</b> ₱{{ number_format($b['room_price'] ?? 0, 2) }}</p>
    <p><b>Paid Amount:</b> ₱{{ number_format($b['paid_amount'] ?? 0, 2) }}</p>

    <p>
        <b>Status:</b>
        <span style="background:#3b82f6;color:white;padding:4px 10px;border-radius:6px;">
            🔵 Checked In
        </span>
    </p>

    <hr>

    {{-- FIX: removed dead checked_out badge — this page only shows checked_in guests --}}
    <a href="/admin/bookings/checkout/{{ $b['id'] }}"
       class="btn btn-danger"
       onclick="return confirm('Check out this guest?')">
        🔴 Check-out Guest
    </a>

</div>

@endforeach

</div>
@endif

@endsection
