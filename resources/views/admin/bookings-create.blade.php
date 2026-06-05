@extends('admin.layout')

@section('content')

<div class="card">
    <h2>➕ Walk-in Booking</h2>

    <form method="POST" action="/admin/bookings/create">
        @csrf

        {{-- GUEST INFO --}}
        <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Guest Name" required>
        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Phone number" required>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email">

        {{-- ROOM SELECT --}}
        <select name="room_id" id="roomSelect" required>
            <option value="">-- Select Room --</option>

            @foreach($rooms as $room)

                @php
                    $category = collect($categories)
                        ->firstWhere('id', $room['category_id']);
                @endphp

                <option 
                    value="{{ $room['uuid_id'] }}"
                    data-price="{{ $category['price'] ?? 0 }}"
                    {{ old('room_id') == $room['uuid_id'] ? 'selected' : '' }}
                >
                    {{ $room['name'] }} (₱{{ $category['price'] ?? 0 }})
                </option>

            @endforeach
        </select>

        {{-- DATES --}}
        <input type="date" name="check_in" id="check_in" required>
        <input type="date" name="check_out" id="check_out" required>

        {{-- 💥 BOOKING CALCULATOR --}}
        <div style="
            background:#0f172a;
            color:white;
            padding:15px;
            border-radius:12px;
            margin-top:15px;">

            <h3>🧮 Booking Calculator</h3>

            <input type="text" id="price" readonly placeholder="Price per Night">
            <input type="text" id="nights" readonly placeholder="Nights">
            <input type="text" id="total" readonly placeholder="Total Amount">
            <input type="text" id="dp" readonly placeholder="Downpayment (50%)">
            <input type="text" id="balance" readonly placeholder="Balance">

        </div>

        {{-- 💳 CASHIER BIG SUMMARY --}}
        <div style="
            margin-top:20px;
            background:#111827;
            color:white;
            padding:20px;
            border-radius:12px;
        ">
<label>Payment Type</label>

<select name="payment_type" id="payment_type" required>
    <option value="full">Full Payment</option>
    <option value="partial">Partial Payment</option>
</select>

            <h3>💳 Cashier Summary</h3>

            <div style="
                display:grid;
                grid-template-columns:1fr 1fr 1fr;
                gap:15px;
                text-align:center;
            ">
            

                {{-- TOTAL --}}
                <div style="background:#1f2937;padding:15px;border-radius:10px;">
                    <small>Total Amount</small>
                    <h2 id="total_amount_display">0.00</h2>
                </div>

                {{-- CASH --}}
                <div style="background:#1f2937;padding:15px;border-radius:10px;">
                    <small>Cash Received</small>
                    <input type="number" id="cash_received"
                           name="cash_received"
                           value="{{ old('cash_received') }}"
                           style="
                                width:100%;
                                padding:8px;
                                margin-top:8px;
                                text-align:center;
                                font-size:18px;
                                border-radius:6px;
                           "
                           required>
                </div>

                {{-- CHANGE --}}
                <div style="background:#1f2937;padding:15px;border-radius:10px;">
                    <small>Change</small>
                    <h2 id="change_display" style="color:#22c55e;">0.00</h2>
                </div>

            </div>

        </div>

        <button type="submit" style="margin-top:15px;">
            Create Booking
        </button>

    </form>
</div>

{{-- DATE VALIDATION --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    let today = new Date().toISOString().split('T')[0];

    document.getElementById("check_in").min = today;
    document.getElementById("check_out").min = today;

    document.getElementById("check_in").addEventListener("change", function () {
        document.getElementById("check_out").min = this.value;
    });

});
</script>

{{-- CALCULATOR + CASHIER LOGIC --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    const roomSelect = document.getElementById("roomSelect");
    const checkIn = document.getElementById("check_in");
    const checkOut = document.getElementById("check_out");

    const price = document.getElementById("price");
    const nights = document.getElementById("nights");
    const total = document.getElementById("total");
    const dp = document.getElementById("dp");
    const balance = document.getElementById("balance");

    const cash = document.getElementById("cash_received");

    let roomPrice = 0;

    function calculate() {

        if (!checkIn.value || !checkOut.value) return;

        let diff = new Date(checkOut.value) - new Date(checkIn.value);
        let nightsCount = diff / (1000 * 60 * 60 * 24);

        if (nightsCount <= 0) return;

        let totalAmount = nightsCount * roomPrice;
        let downpayment = totalAmount * 0.5;

        price.value = roomPrice;
        nights.value = nightsCount;
        total.value = totalAmount.toFixed(2);
        dp.value = downpayment.toFixed(2);
        balance.value = (totalAmount - downpayment).toFixed(2);

        // sync cashier display
        document.getElementById("total_amount_display").innerText = totalAmount.toFixed(2);
    }

    roomSelect.addEventListener("change", function () {
        let selected = this.options[this.selectedIndex];
        roomPrice = Number(selected.dataset.price);

if (!roomPrice || roomPrice <= 0) {
    alert("Invalid room price selected");
    return;
}
        calculate();
    });

    checkIn.addEventListener("change", calculate);
    checkOut.addEventListener("change", calculate);

    cash.addEventListener("input", function () {

        let totalVal = Number(total.value || 0);
        let cashVal = Number(cash.value || 0);

        let change = cashVal - totalVal;

        document.getElementById("change_display").innerText =
            change >= 0 ? change.toFixed(2) : "0.00";
    });

});
</script>

@endsection