@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>📋 Cottage Bookings</h2>
    <a href="/admin/cottage/booking" class="btn">🏠 New Booking</a>
</div>

@if(session('success'))
    <div class="alert-success">✅ {{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">❌ {{ session('error') }}</div>
@endif

{{-- FILTERS --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:120px;">
            <label>Filter</label>
            <select name="filter">
                <option value="upcoming" {{ ($filter ?? '') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="checked_in" {{ ($filter ?? '') === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                <option value="unpaid" {{ ($filter ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="recent" {{ ($filter ?? '') === 'recent' ? 'selected' : '' }}>Recent</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/admin/cottage/bookings" class="btn">Clear</a>
        </div>
    </form>
</div>

{{-- TABLE --}}
@if(empty($bookings))
    <div class="card" style="text-align:center;padding:40px;color:#999;">
        No bookings found.
    </div>
@else
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);font-size:13px;">
    <thead>
        <tr style="background:#0f172a;color:white;">
            <th style="padding:11px 14px;text-align:left;">Guest</th>
            <th style="padding:11px 14px;text-align:left;">Cottage</th>
            <th style="padding:11px 14px;text-align:center;">Check-In</th>
            <th style="padding:11px 14px;text-align:center;">Check-Out</th>
            <th style="padding:11px 14px;text-align:center;">Nights</th>
            <th style="padding:11px 14px;text-align:right;">Amount</th>
            <th style="padding:11px 14px;text-align:center;">Status</th>
            <th style="padding:11px 14px;text-align:center;">Payment</th>
            <th style="padding:11px 14px;text-align:center;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($bookings as $booking)
    <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:10px 14px;font-weight:600;">
            {{ $booking['guest_name'] }}<br>
            <span style="font-size:11px;color:#666;">{{ $booking['guest_phone'] }}</span>
        </td>
        
        <td style="padding:10px 14px;">
            @php
                $cottage = $cottages->firstWhere('id', $booking['cottage_id']);
            @endphp
            {{ $cottage['name'] ?? 'Unknown' }}<br>
            <span style="font-size:11px;color:#666;">₱{{ number_format($booking['price_per_night'], 2) }}/night</span>
        </td>
        
        <td style="padding:10px 14px;text-align:center;font-size:12px;">
            {{ date('M d', strtotime($booking['check_in'])) }}
        </td>
        
        <td style="padding:10px 14px;text-align:center;font-size:12px;">
            {{ date('M d', strtotime($booking['check_out'])) }}
        </td>
        
        <td style="padding:10px 14px;text-align:center;">
            {{ $booking['number_of_nights'] }}
        </td>
        
        <td style="padding:10px 14px;text-align:right;font-weight:600;">
            ₱{{ number_format($booking['total_amount'], 2) }}
        </td>
        
        <td style="padding:10px 14px;text-align:center;">
            @if($booking['booking_status'] === 'checked_out')
                <span style="background:#1a6b3c;color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                    ✓ CHECKED OUT
                </span>
            @elseif($booking['booking_status'] === 'checked_in')
                <span style="background:#0a4a6e;color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                    🏠 CHECKED IN
                </span>
            @else
                <span style="background:#f39c12;color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                    CONFIRMED
                </span>
            @endif
        </td>
        
        <td style="padding:10px 14px;text-align:center;">
            <span style="background:{{ $booking['payment_status'] === 'paid' ? '#1a6b3c' : '#c0392b' }};color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                {{ strtoupper($booking['payment_status']) }}
            </span>
        </td>
        
        <td style="padding:10px 14px;text-align:center;">
            <a href="/admin/cottage/booking/{{ $booking['id'] }}" class="btn" style="padding:4px 8px;font-size:11px;margin:2px;background:#0a4a6e;color:white;display:inline-block;text-decoration:none;border-radius:4px;">View</a>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif
@endsection