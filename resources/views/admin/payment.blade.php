@extends('admin.layout')

@section('content')

<div style="max-width:600px;margin:auto;padding:20px;">

    <h2>💳 Payment Panel</h2>

    <div style="background:#f8f8f8;padding:15px;border-radius:10px;margin-bottom:20px;">
        <p><b>Guest:</b> {{ $booking['full_name'] }}</p>
        <p><b>Room:</b> {{ $booking['room_name'] }}</p>
        <p><b>Total Amount:</b> ₱{{ number_format($booking['total_amount'], 2) }}</p>
        <p><b>Paid:</b> ₱{{ number_format($booking['paid_amount'] ?? 0, 2) }}</p>
        <p><b>Balance:</b> ₱{{ number_format($booking['balance_amount'], 2) }}</p>
    </div>

    {{-- FIX: removed broken/duplicate closing </div> tags that were outside the form --}}
    <form method="POST" action="/admin/bookings/payment/{{ $booking['id'] }}">
        @csrf

        <label>Payment Type</label>
        <select name="payment_type" id="payment_type">
            <option value="full">Full Payment</option>
            <option value="partial">Partial Payment</option>
        </select>

        <label>Cash Received</label>
        <input type="number" name="cash_received" id="cash_received" min="0" step="0.01" required>

        <div style="display:flex;gap:10px;margin-top:5px;">
            <div style="flex:1;">
                <label>Change</label>
                <input type="number" id="change" readonly>
            </div>
            <div style="flex:1;">
                <label>Remaining Balance</label>
                <input type="number" id="balance"
                       value="{{ $booking['balance_amount'] }}"
                       readonly>
            </div>
        </div>

        <button type="submit" class="btn btn-success"
                style="width:100%;margin-top:20px;padding:12px;font-size:15px;">
            ✅ Confirm Payment
        </button>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const cash        = document.getElementById('cash_received');
    const balanceEl   = document.getElementById('balance');
    const changeEl    = document.getElementById('change');
    const paymentType = document.getElementById('payment_type');

    // FIX: store original DB balance as source of truth — never mutate this
    const dbBalance = Number(balanceEl.value || 0);

    function reset() {
        balanceEl.value = dbBalance;
        cash.value      = '';
        changeEl.value  = 0;
    }

    cash.addEventListener('input', function () {
        let cashVal = Number(this.value || 0);

        if (cashVal >= dbBalance) {
            changeEl.value  = (cashVal - dbBalance).toFixed(2);
            balanceEl.value = 0;
        } else {
            changeEl.value  = 0;
            balanceEl.value = (dbBalance - cashVal).toFixed(2);
        }
    });

    // FIX: reset on payment type change so numbers don't carry over
    paymentType.addEventListener('change', reset);
});
</script>

@endsection
