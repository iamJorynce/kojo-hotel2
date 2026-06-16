@extends('admin.layout')

@section('content')

<h2 style="margin-bottom:20px;">🏨 Hotel Dashboard</h2>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">

    <div class="card">
        <h3>Total Rooms</h3>
        <p style="font-size:28px;font-weight:bold;">{{ $totalRooms }}</p>
    </div>

    <div class="card">
        <h3>Available Rooms</h3>
        <p style="font-size:28px;font-weight:bold;color:green;">{{ $availableRooms }}</p>
    </div>

    <div class="card">
        <h3>Occupied Rooms</h3>
        {{-- FIX: was passing Collection object — web.php now passes ->count() so this is safe --}}
        <p style="font-size:28px;font-weight:bold;color:red;">{{ $occupiedRooms }}</p>
    </div>

    <div class="card">
        <h3>Pending Bookings</h3>
        <p style="font-size:28px;font-weight:bold;color:orange;">{{ $pendingBookings }}</p>
    </div>

    <div class="card">
        <h3>Confirmed Bookings</h3>
        <p style="font-size:28px;font-weight:bold;color:blue;">{{ $confirmedBookings }}</p>
    </div>

    <div class="card">
        <h3>Today Check-ins</h3>
        {{-- FIX: was printing Collection object — call ->count() here --}}
        <p style="font-size:28px;font-weight:bold;">{{ $todayCheckins->count() }}</p>
    </div>

    <div class="card">
        <h3>Today Check-outs</h3>
        {{-- FIX: same as above --}}
        <p style="font-size:28px;font-weight:bold;">{{ $todayCheckouts->count() }}</p>
    </div>

</div>

{{-- TODAY CHECK-INS LIST --}}
@if($todayCheckins->count() > 0)
<div class="card" style="margin-top:25px;">
    <h3 style="margin-bottom:12px;">📋 Today's Check-ins</h3>
    @foreach($todayCheckins as $b)
        <div style="padding:8px 0;border-bottom:1px solid #eee;font-size:14px;">
            <b>{{ $b['full_name'] }}</b> — {{ $b['room_name'] ?? '' }} Room {{ $b['room_number'] ?? '' }}
            <span style="color:#0a4a6e;margin-left:8px;">{{ $b['check_in'] }} → {{ $b['check_out'] }}</span>
        </div>
    @endforeach
</div>
@endif

{{-- TODAY CHECK-OUTS LIST --}}
@if($todayCheckouts->count() > 0)
<div class="card" style="margin-top:15px;">
    <h3 style="margin-bottom:12px;">📋 Today's Check-outs</h3>
    @foreach($todayCheckouts as $b)
        <div style="padding:8px 0;border-bottom:1px solid #eee;font-size:14px;">
            <b>{{ $b['full_name'] }}</b> — {{ $b['room_name'] ?? '' }} Room {{ $b['room_number'] ?? '' }}
            <span style="color:#c62828;margin-left:8px;">Due out: {{ $b['check_out'] }}</span>
        </div>
    @endforeach
</div>
@endif

@endsection
