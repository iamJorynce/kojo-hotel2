@extends('admin.layout')

@section('content')

<div class="container" style="max-width:600px;margin:auto;padding:20px;">

    <h2>💳 Payment Panel</h2>

    <div style="background:#f8f8f8;padding:15px;border-radius:10px;margin-bottom:15px;">
        <p><b>Guest:</b> {{ $booking['full_name'] }}</p>

        <p><b>Room:</b> {{ $booking['room_name'] }}</p>

        <p><b>Total Amount:</b> ₱{{ $booking['total_amount'] }}</p>

        <p><b>Paid:</b> ₱{{ $booking['paid_amount'] ?? 0 }}</p>

        <p><b>Balance:</b> ₱{{ $booking['balance_amount'] }}</p>
    </div>

    <form method="POST" action="/admin/bookings/payment/{{ $booking['id'] }}">
        @csrf
    
        <label>Payment Type</label>
        <select name="payment_type">
            <option value="full">Full Payment</option>
            <option value="partial">Partial Payment</option>
        </select>

        <br><br>

        <label>Cash Received</label>
        <input type="number" name="cash_received" id="cash_received">

        <br><br>

        <div style="display:flex;gap:10px;">
    <div style="flex:1;">
        <label>Change</label>
        <input type="number" id="change" readonly>
    </div>

    <div style="flex:1;">
        <label>Balance</label>
        <input type="number" id="balance" value="{{ $booking['balance_amount'] }}" readonly>
    </div>
</div>  {{-- ✅ close the flex container here --}}

<br>

<button type="submit" class="pay-btn">
    Select for Payment
</button>
</div>
</div>

    </form>

</div>
<script>
function setBookingAmount(total, balance) {

    console.log("CLICKED:", total, balance);

    document.getElementById("balance").value = balance;
    document.getElementById("cash_received").value = "";
    document.getElementById("change").value = 0;
}

document.getElementById("cash_received").addEventListener("input", function () {

    let cash = Number(this.value || 0);
    let balance = Number(document.getElementById("balance").value || 0);

    let remaining = balance - cash;

    document.getElementById("balance").value =
        remaining > 0 ? remaining : 0;

    document.getElementById("change").value =
        cash > balance ? cash - balance : 0;
});

document.addEventListener("DOMContentLoaded", function () {

    const cash = document.getElementById("cash_received");
    const balanceInput = document.getElementById("balance");
    const changeInput = document.getElementById("change");
    const paymentType = document.querySelector("select[name='payment_type']");

    // 💥 SOURCE OF TRUTH (NEVER CHANGE THIS)
    const dbBalance = Number(balanceInput.value || 0);

    let currentBalance = dbBalance;

    // 💥 RESET FUNCTION
    function resetBalance() {
        currentBalance = dbBalance;
        balanceInput.value = dbBalance;
        cash.value = "";
        changeInput.value = 0;
    }

    // 💥 CASH COMPUTE
    cash.addEventListener("input", function () {

        let cashValue = Number(this.value || 0);
        let remaining = dbBalance - cashValue;

        if (remaining <= 0) {
            changeInput.value = (cashValue - dbBalance).toFixed(2);
            balanceInput.value = 0;
        } else {
            changeInput.value = 0;
            balanceInput.value = remaining;
        }
    });

    // 💥 PAYMENT TYPE CHANGE = RESET EVERYTHING
    paymentType.addEventListener("change", function () {
        console.log("RESET TRIGGERED");
        resetBalance();
    });

});

</script>

@endsection

