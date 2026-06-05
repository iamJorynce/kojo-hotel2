@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>Confirmed Bookings</h2>
</div>

@if(empty($bookings))
    <p>No confirmed bookings.</p>
@else

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;">
<div class="card" style="margin-bottom:20px;">

    <h2>💳 Cashier Payment Panel</h2>

    <label>Total Amount</label>
    <input type="number" id="total_amount" readonly>
    <br>
    <label>Cash Received</label>
    <input type="number" id="cash_received">

    <div style="display:flex;gap:10px;margin-top:10px;">

        <div style="flex:1;">
            <label>Change</label>
            <input type="number" id="change" readonly>
        </div>

        <div style="flex:1;">
            <label>Balance (Kulang)</label>
            <input type="number" id="balance" readonly>
        </div>

    </div>

</div>


@foreach($bookings as $b)

@php
$status = $b['status'] ?? 'pending';
$payment = $b['payment_status'] ?? 'unpaid';
@endphp


<div class="card"
     style="@if($status === 'checked_in') border-left:5px solid #3b82f6; @endif">

    {{-- GUEST INFO --}}
    <h3>{{ $b['full_name'] }}</h3>
    <p><b>Phone:</b> {{ $b['phone'] }}</p>
    <p><b>Email:</b> {{ $b['email'] }}</p>
    <p><b>Room:</b> {{ $b['room_name'] }} (Room Number: {{ $b['room_number'] ?? 'N/A' }})</p>
    <p><b>Check In:</b> {{ $b['check_in'] }}</p>
    <p><b>Check Out:</b> {{ $b['check_out'] }}</p>
    <p><b>Room Price per night:</b> {{ $b['room_price'] }} x {{ $b['nights'] }} nights</p> 
    <p><b>Room total amount:</b> {{ $b['total_amount'] }}</p>
    <p><b>Paid Amount:</b> {{ $b['paid_amount'] }}</p>
    <p><b>Balance:</b> {{ $b['balance_amount'] }}</p>
    



    {{-- STATUS --}}
    <p><b>Status:</b>

        @if($status === 'checked_in')
            <span style="background:#3b82f6;color:#fff;padding:5px 10px;border-radius:6px;">
                 Checked In
            </span>

        @elseif($status === 'confirmed')
                Confirmed
            

        @else
            <span style="background:#f59e0b;color:#fff;padding:5px 10px;border-radius:6px;">
                🟡 Pending
            </span>
        @endif

    </p>

    {{-- PAYMENT --}}

    <button 
    class="btn btn-primary"
    onclick="setBookingAmount({{ $b['total_amount'] }}, {{ $b['balance_amount'] }})">
    Select for Payment
    
</button>

    <p><b>Payment:</b>

        @if($payment === 'paid')
            💵 FULLY PAID
            

        @elseif($payment === 'partial')
            <span style="background:#fff3cd;color:#856404;padding:5px 10px;border-radius:6px;">
                💰 PARTIAL PAYMENT
            </span>

        @else
            <span style="background:#dc2626;color:white;padding:5px 10px;border-radius:6px;">
                ⏳ UNPAID
            </span>
        @endif

    </p>

    {{-- ACTION BUTTONS --}}

    {{-- MARK AS PAID --}}
    @if($status === 'confirmed' && $payment === 'unpaid')
        <a href="/admin/bookings/pay/{{ $b['id'] }}"
           class="btn btn-warning"
           onclick="return confirm('Mark as PAID?')">
            💳 Mark as Paid
        </a>
    @endif

    {{-- CHECK-IN --}}
    @if($status === 'confirmed' && $payment === 'paid')
        <a href="/admin/bookings/checkin/{{ $b['id'] }}"
           class="btn btn-success"
           onclick="return confirm('Check-in guest?')">
            🟢 Check-in
        </a>
    @endif

    {{-- CHECKED IN STATE --}}
    @if($status === 'checked_in')
        <div style="margin-top:10px;">
            <strong style="color:#3b82f6;">
                ✅ Guest already checked in
            </strong>
        </div>
    @endif

</div>

@endforeach

</div>
@endif

@endsection

<script>
    
// 👇 PUT YOUR CASHIER JS HERE
function setBookingAmount(total, balance) {

    document.getElementById("total_amount").value = total;
    document.getElementById("balance").value = balance;

    document.getElementById("cash_received").value = "";
    document.getElementById("change").value = 0;
}

document.addEventListener("DOMContentLoaded", function () {

    const cash = document.getElementById("cash_received");
    const total = document.getElementById("total_amount");
    const change = document.getElementById("change");
    const balance = document.getElementById("balance");

    cash.addEventListener("input", function () {

        let t = Number(total.value || 0);
        let c = Number(cash.value || 0);

        if (c >= t) {
            change.value = (c - t).toFixed(2);
            balance.value = 0;
        } else {
            change.value = 0;
            balance.value = (t - c).toFixed(2);
        }
    });
    console.log("ROOM PRICE:", roomPrice);

});
</script>