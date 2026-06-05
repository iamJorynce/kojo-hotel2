@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>Checked-In Guests</h2>
</div>

@if(empty($bookings))
    <p>No checked-in guests.</p>
@else

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;">

@foreach($bookings as $b)

@php
$status = $b['status'] ?? 'pending';
$payment = $b['payment_status'] ?? 'unpaid';
@endphp


<div class="card" style="border-left:5px solid #3b82f6;">

@if($b['status'] === 'checked_in')

    <a href="/admin/bookings/checkout/{{ $b['id'] }}"
       class="btn btn-danger"
       onclick="return confirm('Check-out guest?')">
        🔴 Check-out
    </a>

@endif

@if($status === 'checked_out')
    <span style="background:#ef4444;color:#fff;padding:5px 10px;border-radius:6px;">
        🔴 Checked Out
    </span>
@endif

    <h3>{{ $b['full_name'] }}</h3>
    <p><b>Phone:</b> {{ $b['phone'] }}</p>
    <p><b>Email:</b> {{ $b['email'] }}</p>
    <p><b>Room:</b> {{ $b['room_name'] }} (Room Number: {{ $b['room_number'] ?? '' }})</p>
    <p><b>Check In:</b> {{ $b['check_in'] }}</p>
    <p><b>Check Out:</b> {{ $b['check_out'] }}</p>
    <p><b>Room Price per night:</b> {{ $b['room_price'] }}</p>
    <p><b>Paid Amount:</b> {{ $b['paid_amount'] }}</p>

    <p>
        <b>Status:</b>
         🔵 Checked In
    </p>

</div>


@endforeach

</div>

@endif

@endsection