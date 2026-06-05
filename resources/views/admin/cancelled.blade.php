@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>Cancelled Bookings</h2>
</div>

@if(empty($bookings))

<p>No cancelled bookings.</p>

@else

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;">

@foreach($bookings as $b)

<div class="card">

    <h3>{{ $b['full_name'] }}</h3>

    <p><b>Email:</b> {{ $b['email'] }}</p>

    <p><b>Room:</b> {{ $b['room_name'] ?? $b['room_id'] }}</p>

    <p>
        <b>Status:</b>
        <span style="background:red;color:white;padding:5px 10px;border-radius:5px;">
            Cancelled
        </span>
    </p>

</div>

@endforeach

</div>

@endif

@endsection