@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>Bookings</h2>
</div>

@if(session('success'))
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

@php
$current = $status ?? 'all';
@endphp




<!-- TABS -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">

    <a href="/admin/bookings?status=all"
       style="padding:8px 12px;border-radius:6px;
       background: {{ $current=='all' ? '#0a4a6e' : '#eee' }};
       color: {{ $current=='all' ? '#fff' : '#000' }};">All</a>

    <a href="/admin/bookings?status=pending"
       style="padding:8px 12px;border-radius:6px;
       background: {{ $current=='pending' ? '#0a4a6e' : '#eee' }};
       color: {{ $current=='pending' ? '#fff' : '#000' }};">Pending</a>

    <a href="/admin/bookings?status=confirmed"
       style="padding:8px 12px;border-radius:6px;
       background: {{ $current=='confirmed' ? '#0a4a6e' : '#eee' }};
       color: {{ $current=='confirmed' ? '#fff' : '#000' }};">Confirmed</a>

    <a href="/admin/bookings?status=checked_in"
       style="padding:8px 12px;border-radius:6px;
       background: {{ $current=='checked_in' ? '#0a4a6e' : '#eee' }};
       color: {{ $current=='checked_in' ? '#fff' : '#000' }};">Checked-in</a>

    <a href="/admin/bookings?status=checked_out"
       style="padding:8px 12px;border-radius:6px;
       background: {{ $current=='checked_out' ? '#0a4a6e' : '#eee' }};
       color: {{ $current=='checked_out' ? '#fff' : '#000' }};">Checked-out</a>

</div>

<!-- EMPTY CHECK -->
@if(empty($bookings) || count($bookings) == 0)
    <p>No bookings found.</p>
@else

<!-- GRID -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:20px;">

@php
    $activeBookings = collect($bookings)->where('status', '!=', 'cancelled');
@endphp


@foreach($activeBookings as $b)
@php
$statusColors = [
    'pending' => 'orange',
    'confirmed' => 'green',
    'checked_in' => 'blue',
    'checked_out' => 'gray'
];

$color = $statusColors[$b['status']] ?? 'black';
@endphp

@php
$status = strtolower($b['status'] ?? 'pending');
$payment = strtolower($b['payment_status'] ?? 'unpaid');

$total = $b['total_amount'] ?? ($b['room_price'] ?? 0);
$dp = $b['downpayment_amount'] ?? ($total * 0.5);
$balance = $b['balance_amount'] ?? ($total - $dp);
$paid_amount = $b['paid_amount'];
@endphp



<div class="card" style="border-left:6px solid 
@if($status == 'confirmed') green
@elseif($status == 'checked_in') blue
@elseif($status == 'checked_out') gray
@else orange
@endif;
padding:15px;border-radius:10px;margin-bottom:15px;">

    <h3>{{ $b['full_name'] }}</h3>
    
    <p><b>Phone:</b> {{ $b['phone'] }}</p>


    <p><b>Room:</b> {{ $b['room_name'] }}</p>
    <p><b>Room No:</b> {{ $b['room_number'] }}</p>
<p><b>Check in date:</b> {{ $b['check_in'] }}</p>
<p><b>Check in date:</b> {{ $b['check_out'] }}</p>
    <hr>

    {{-- 💰 AMOUNTS --}}
    <p><b>Room Total Amount :</b> ₱{{ number_format($total, 2) }}</p>
    <p><b>Downpayment (50%):</b> ₱{{ number_format($dp, 2) }}</p>
    <p><b>Balance:</b> ₱{{ number_format($balance, 2) }}</p>

    <hr>

    {{-- STATUS --}}
    
<p><b>Paid Amount:</b> ₱{{ number_format($paid_amount, 2) }}</p>
<p><b>Payment Status:</b>
    <span style="text-transform:capitalize;">{{ str_replace('_',' ',$payment) }}</span>
</p>

<p><b>Status:</b>
    <span style="background:{{ $color }};color:white;padding:5px 10px;border-radius:8px;">
    <span style="text-transform:capitalize;">{{ str_replace('_',' ',$status) }}</span>
</p>
    <hr>

    @if($b['status'] == 'cancelled')
    <span style="color:red;">Cancelled</span>
@endif

@if($b['status'] !== 'checked_in' && $b['payment_status'] !== 'paid')

    <a href="/admin/bookings/cancel/{{ $b['id'] }}"
       onclick="return confirm('Cancel this booking?')"
       style="background:red;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
        ❌ Cancel Booking
    </a>

@endif
<br>
<br>
{{-- 🔵 CHECKED OUT → NO BUTTONS --}}
@if(($b['status'] ?? '') === 'checked_out')

    <span style="color:gray;font-weight:bold;">
        ✔ Guest already checked out
    </span>

@elseif(($b['payment_status'] ?? '') === 'paid' && ($b['balance_amount'] ?? 0) == 0)

    <span style="color:green;font-weight:bold;">
        ✔ Fully Paid
    </span>

@elseif(($b['status'] ?? '') === 'checked_in')

    <a href="/admin/bookings/checkout/{{ $b['id'] }}"
       style="background:red;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
        🔵 Check Out
    </a>

@elseif(($b['status'] ?? '') === 'confirmed')

    @if(($b['payment_status'] ?? '') === 'partial' || ($b['payment_status'] ?? '') === 'unpaid' || ($b['balance_amount'] ?? 0) > 0)

        <a href="/admin/bookings/payment/{{ $b['id'] }}"
           style="background:green;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
            Proceed for Payment
        </a>

    @else

        <a href="/admin/bookings/checkin/{{ $b['id'] }}"
           style="background:blue;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
            Check In
        </a>

    @endif

@elseif(!($b['has_conflict'] ?? false))

    <a href="/admin/bookings/payment/{{ $b['id'] }}"
       style="background:green;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
        Proceed for Payment
    </a>

@else

    <span style="color:red;font-weight:bold;">
        ❌ Conflict Booking (Already Reserved)
    </span>

@endif



</div>
@endforeach

</div>

@endif

<script>
function confirmBooking(id, name) {
    if (confirm("Confirm booking for " + name + "?")) {
        window.location.href = "/admin/bookings/confirm/" + id;
    }
}
</script>

<script>
setInterval(() => {
    fetch(window.location.href)
        .then(res => res.text())
        .then(html => {
            document.open();
            document.write(html);
            document.close();
        });
}, 15000); // every 15 seconds
</script>

@endsection