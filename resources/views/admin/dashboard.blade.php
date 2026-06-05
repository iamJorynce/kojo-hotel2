@extends('admin.layout')

@section('content')

<h2 style="margin-bottom:20px;">Hotel Dashboard</h2>

<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px;">

    <div class="card">
        <h3>Total Rooms</h3>
        <p style="font-size:24px;">{{ $totalRooms }}</p>
    </div>

    <div class="card">
        <h3>Available Rooms</h3>
        <p style="font-size:24px; color:green;">{{ $availableRooms }}</p>
    </div>

    <div class="card">
        <h3>Occupied Rooms</h3>
        <p style="font-size:24px; color:red;">{{ $occupiedRooms }}</p>
    </div>

    <div class="card">
        <h3>Pending Bookings</h3>
        <p style="font-size:24px; color:orange;">{{ $pendingBookings }}</p> 
    </div>

    <div class="card">
        <h3>Confirmed Bookings</h3>
        <p style="font-size:24px; color:blue;">{{ $confirmedBookings }}</p>
    </div>

    <div class="card">
        <h3>Today Check-ins</h3>
        <p style="font-size:24px;">{{ $todayCheckins }}</p>
    </div>

    <div class="card">
        <h3>Today Check-outs</h3>
        <p style="font-size:24px;">{{ $todayCheckouts }}</p>
    </div>

</div>

@endsection