@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>✅ Confirmed Bookings</h2>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

@if(empty($bookings))
    <p>No confirmed bookings.</p>
@else



<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;">

@foreach($bookings as $b)

@php
$status  = $b['status']         ?? 'pending';
$payment = $b['payment_status'] ?? 'unpaid';
$total   = (float) ($b['total_amount']   ?? 0);
$paid    = (float) ($b['paid_amount']    ?? 0);
$balance = (float) ($b['balance_amount'] ?? ($total - $paid));
$isFullyPaid = ($payment === 'paid' && $balance <= 0);
@endphp

<div class="card" style="border-left:5px solid {{ $status === 'checked_in' ? '#3b82f6' : 'green' }};">

    <h3>{{ $b['full_name'] }}</h3>
    <p><b>Phone:</b> {{ $b['phone'] ?? '-' }}</p>
    <p><b>Email:</b> {{ $b['email'] ?? '-' }}</p>
    <p><b>Room:</b> {{ $b['room_name'] ?? '-' }} (Room No: {{ $b['room_number'] ?? 'N/A' }})</p>
    <p><b>Check-in:</b> {{ $b['check_in'] }}</p>
    <p><b>Check-out:</b> {{ $b['check_out'] }}</p>
    <p><b>Price/night:</b> ₱{{ number_format($b['room_price'] ?? 0, 2) }} × {{ $b['nights'] ?? 0 }} nights</p>
    <p><b>Total:</b> ₱{{ number_format($total, 2) }}</p>
    <p><b>Paid:</b> ₱{{ number_format($paid, 2) }}</p>
    <p><b>Balance:</b> ₱{{ number_format($balance, 2) }}</p>

    <hr>

    {{-- PAYMENT STATUS --}}
    <p><b>Payment:</b>
        @if($isFullyPaid)
            <span style="color:green;font-weight:bold;">💵 FULLY PAID</span>
        @elseif($payment === 'partial')
            <span style="background:#fff3cd;color:#856404;padding:4px 10px;border-radius:6px;">💰 PARTIAL</span>
        @else
            <span style="background:#dc2626;color:white;padding:4px 10px;border-radius:6px;">⏳ UNPAID</span>
        @endif
    </p>

    {{-- STATUS --}}
    <p><b>Status:</b>
        @if($status === 'checked_in')
            <span style="background:#3b82f6;color:white;padding:4px 10px;border-radius:6px;">✅ Checked In</span>
        @else
            <span style="background:green;color:white;padding:4px 10px;border-radius:6px;">Confirmed</span>
        @endif
    </p>

    <hr>

    {{-- ACTION BUTTONS --}}
    @if($status === 'checked_in')
        <div style="color:#3b82f6;font-weight:bold;">✅ Guest already checked in</div>

    @elseif($isFullyPaid)
        {{-- Fully paid + confirmed → ready to check in --}}
        <a href="/admin/bookings/checkin/{{ $b['id'] }}"
           class="btn btn-success"
           onclick="return confirm('Check in guest?')">
            🟢 Check-in
        </a>

    @else
        {{-- Has balance → go to payment --}}
        <button class="btn btn-primary"
                onclick="selectBooking('{{ $b['full_name'] }}', {{ $total }}, {{ $balance }})">
            💳 Select for Payment
        </button>

        <a href="/admin/bookings/payment/{{ $b['id'] }}"
           class="btn"
           style="margin-top:8px;display:inline-block;">
            🧾 Open Payment Page
        </a>
    @endif

</div>

@endforeach
</div>
@endif

@endsection

<script>
function selectBooking(name, total, balance) {
    document.getElementById('selected_guest').value = name;
    document.getElementById('total_amount').value   = total;
    document.getElementById('balance').value        = balance;
    document.getElementById('cash_received').value  = '';
    document.getElementById('change').value         = 0;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', function () {
    const cash    = document.getElementById('cash_received');
    const balance = document.getElementById('balance');
    const change  = document.getElementById('change');

    cash.addEventListener('input', function () {
        // FIX: removed console.log("ROOM PRICE:", roomPrice) — roomPrice undefined here
        let c = Number(this.value   || 0);
        let b = Number(balance.value || 0);

        if (c >= b) {
            change.value  = (c - b).toFixed(2);
            balance.value = 0;
        } else {
            change.value  = 0;
            balance.value = (b - c).toFixed(2);
        }
    });
});
</script>
