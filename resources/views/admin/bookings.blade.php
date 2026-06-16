@extends('admin.layout')

@section('content')

@if($errors->any())
    <div style="background:#fadbd8;border:2px solid #c0392b;padding:16px;border-radius:8px;margin-bottom:20px;">
        <p style="margin:0;color:#c0392b;font-weight:600;">❌ Error:</p>
        @foreach($errors->all() as $error)
            <p style="margin:4px 0 0 0;color:#c0392b;font-size:13px;">{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="topbar">
    <h2>Bookings</h2>
</div>

@if(session('success'))
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:6px;margin-bottom:15px;">
        {{ session('error') }}
    </div>
@endif

@php
$current = $status ?? 'all';
@endphp

<!-- TABS -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">

    <a href="/admin/bookings?status=all"
       style="padding:8px 12px;border-radius:6px;
       background:{{ $current=='all' ? '#0a4a6e' : '#eee' }};
       color:{{ $current=='all' ? '#fff' : '#000' }};">All</a>

    <a href="/admin/bookings?status=pending"
       style="padding:8px 12px;border-radius:6px;
       background:{{ $current=='pending' ? '#0a4a6e' : '#eee' }};
       color:{{ $current=='pending' ? '#fff' : '#000' }};">Pending</a>

    <a href="/admin/bookings?status=confirmed"
       style="padding:8px 12px;border-radius:6px;
       background:{{ $current=='confirmed' ? '#0a4a6e' : '#eee' }};
       color:{{ $current=='confirmed' ? '#fff' : '#000' }};">Confirmed</a>

    <a href="/admin/bookings?status=checked_in"
       style="padding:8px 12px;border-radius:6px;
       background:{{ $current=='checked_in' ? '#0a4a6e' : '#eee' }};
       color:{{ $current=='checked_in' ? '#fff' : '#000' }};">Checked-in</a>

    <a href="/admin/bookings?status=checked_out"
       style="padding:8px 12px;border-radius:6px;
       background:{{ $current=='checked_out' ? '#0a4a6e' : '#eee' }};
       color:{{ $current=='checked_out' ? '#fff' : '#000' }};">Checked-out</a>

</div>

<!-- SEARCH BAR -->
<div style="margin-bottom:20px;">
    <input type="text" id="guestSearch"
           placeholder="🔍 Search guest name..."
           oninput="filterBookings()"
           style="width:100%;max-width:400px;padding:10px 14px;border-radius:8px;
                  border:1px solid #ccc;font-size:14px;outline:none;">
</div>


<!-- EMPTY CHECK -->
@if(empty($bookings) || count($bookings) == 0)
    <p>No bookings found.</p>
@else

@php
    // FIX: removed dead cancelled check inside loop — filter here is enough
    $activeBookings = collect($bookings)->where('status', '!=', 'cancelled');
@endphp

<!-- GRID -->
<div class="bookings-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:20px;">

@foreach($activeBookings as $b)

@php
$status  = strtolower($b['status']         ?? 'pending');
$payment = strtolower($b['payment_status'] ?? 'unpaid');

$total   = (float) ($b['total_amount'] ?? $b['room_price'] ?? 0);

// FIX: downpayment display uses paid_amount as source of truth, not hardcoded 50%
$paid_amount = (float) ($b['paid_amount']    ?? 0); // FIX: added ?? 0 fallback
$balance     = (float) ($b['balance_amount'] ?? ($total - $paid_amount));

$statusColors = [
    'pending'     => 'orange',
    'confirmed'   => 'green',
    'checked_in'  => 'blue',
    'checked_out' => 'gray',
];
$color = $statusColors[$status] ?? 'black';

// fully paid = payment_status is paid AND balance is 0
$isFullyPaid = ($payment === 'paid' && $balance <= 0);
@endphp

<div class="card booking-card"
     data-name="{{ strtolower($b['full_name']) }}"
     style="border-left:6px solid {{ $color }};padding:15px;border-radius:10px;margin-bottom:15px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,0.07);">


    <h3 style="margin:0 0 10px;">{{ $b['full_name'] }}</h3>

    <p><b>Phone:</b> {{ $b['phone'] ?? '-' }}</p>
    <p><b>Room:</b> {{ $b['room_name'] ?? '-' }}</p>
    <p><b>Room No:</b> {{ $b['room_number'] ?? '-' }}</p>

    {{-- FIX: second label was also "Check in date" — corrected to "Check out date" --}}
    <p><b>Check-in Date:</b> {{ $b['check_in'] }}</p>
    <p><b>Check-out Date:</b> {{ $b['check_out'] }}</p>

    <hr>

    <p><b>Total Amount:</b> ₱{{ number_format($total, 2) }}</p>
    <p><b>Paid Amount:</b> ₱{{ number_format($paid_amount, 2) }}</p>
    <p><b>Balance:</b> ₱{{ number_format($balance, 2) }}</p>

    <hr>

    <p><b>Payment Status:</b>
        <span style="text-transform:capitalize;">{{ str_replace('_', ' ', $payment) }}</span>
    </p>

    {{-- FIX: closed the outer span properly --}}
    <p><b>Status:</b>
        <span style="background:{{ $color }};color:white;padding:4px 10px;border-radius:8px;text-transform:capitalize;display:inline-block;">
            {{ str_replace('_', ' ', $status) }}
        </span>
    </p>

    <hr>

    {{-- CANCEL BUTTON
         FIX: also exclude checked_out and cancelled from showing cancel button --}}
    @if(!in_array($status, ['checked_in', 'checked_out', 'cancelled']) && !$isFullyPaid)
        <a href="/admin/bookings/cancel/{{ $b['id'] }}"
           onclick="return confirm('Cancel this booking?')"
           style="background:red;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;display:inline-block;margin-bottom:10px;">
            ❌ Cancel Booking
        </a>
    @endif

    <br>

    {{-- ACTION BUTTONS --}}

    @if($status === 'checked_out')

        {{-- Already done --}}
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <span style="color:gray;font-weight:bold;">✔ Guest already checked out</span>
        <a href="/admin/bookings/receipt/{{ $b['id'] }}"
           target="_blank"
           style="background:#0a4a6e;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:14px;">
            🖨️ Print Receipt
        </a>
    </div>

    @elseif($isFullyPaid && $status === 'checked_in')

        {{-- Checked in + paid → show checkout --}}
        <a href="/admin/bookings/checkout/{{ $b['id'] }}"
           style="background:#c0392b;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
            🔵 Check Out
        </a>

    @elseif($isFullyPaid && $status === 'confirmed')

        {{-- Confirmed + fully paid → ready to check in --}}
        <a href="/admin/bookings/checkin/{{ $b['id'] }}"
           style="background:blue;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
            ✅ Check In
        </a>

    @elseif($status === 'confirmed' && !$isFullyPaid)

        {{-- Confirmed but not fully paid → go to payment --}}
        <a href="/admin/bookings/payment/{{ $b['id'] }}"
           style="background:green;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
            💳 Proceed to Payment
        </a>

    @elseif($status === 'pending' && !($b['has_conflict'] ?? false))

        {{-- Pending, no conflict → go to payment to confirm --}}
        <a href="/admin/bookings/payment/{{ $b['id'] }}"
           style="background:green;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;">
            💳 Proceed to Payment
        </a>

    @elseif($b['has_conflict'] ?? false)

        {{-- Conflict detected --}}
        <span style="color:red;font-weight:bold;">❌ Conflict — Room Already Reserved</span>

    @endif

</div>
@endforeach

</div>
@endif



{{-- FIX: removed dead confirmBooking() function — nothing called it
     FIX: auto-refresh now targets .bookings-grid which actually exists --}}
<script>

// ── SEARCH FILTER ──────────────────────────────────────────────
function filterBookings() {
    const query = document.getElementById('guestSearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.booking-card');

    cards.forEach(card => {
        const name = card.dataset.name || '';
        card.style.display = name.includes(query) ? '' : 'none';
    });
}

// ── AUTO REFRESH ───────────────────────────────────────────────
setInterval(() => {
    // Don't refresh kung nag-type ang user — ma-reset ang search
    if (document.getElementById('guestSearch').value.trim() !== '') return;

    fetch(window.location.href)
        .then(res => res.text())
        .then(html => {
            const parser  = new DOMParser();
            const doc     = parser.parseFromString(html, 'text/html');
            const newData = doc.querySelector('.bookings-grid');
            const oldData = document.querySelector('.bookings-grid');
            if (newData && oldData) {
                oldData.innerHTML = newData.innerHTML;
            }
        });
}, 15000);

</script>


@endsection
