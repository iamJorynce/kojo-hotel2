@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>💳 Day Tour Payment</h2>
    <a href="/admin/day-tours" class="btn">← Back</a>
</div>

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div style="max-width:600px;margin:auto;">

    {{-- BOOKING SUMMARY --}}
    <div class="card" style="border-top:4px solid #0a4a6e;margin-bottom:20px;">
        <h3 style="margin-bottom:14px;">📋 Booking Summary</h3>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <tr>
                <td style="padding:6px 0;color:#888;width:140px;">Guest</td>
                <td style="padding:6px 0;font-weight:600;">{{ $tour['full_name'] }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#888;">Phone</td>
                <td style="padding:6px 0;">{{ $tour['phone'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#888;">Package</td>
                <td style="padding:6px 0;">{{ $tour['package_name'] }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#888;">Visit Date</td>
                <td style="padding:6px 0;">{{ $tour['visit_date'] }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#888;">Guests</td>
                <td style="padding:6px 0;font-weight:600;">{{ $tour['guest_count'] }} person(s)</td>
            </tr>
            <tr style="border-top:1px solid #eee;">
                <td style="padding:10px 0 4px;color:#888;">Total Amount</td>
                <td style="padding:10px 0 4px;font-size:20px;font-weight:700;color:#0a4a6e;">
                    ₱{{ number_format($tour['total_amount'], 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding:4px 0;color:#888;">Already Paid</td>
                <td style="padding:4px 0;color:#1a6b3c;font-weight:600;">
                    ₱{{ number_format($tour['paid_amount'] ?? 0, 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding:4px 0;color:#888;">Balance</td>
                <td style="padding:4px 0;color:#c0392b;font-weight:700;font-size:18px;">
                    ₱{{ number_format($tour['balance_amount'] ?? 0, 2) }}
                </td>
            </tr>
        </table>
    </div>

    {{-- PAYMENT FORM --}}
    <div class="card">
        <h3 style="margin-bottom:18px;">💵 Accept Payment</h3>

        <form method="POST" action="/admin/day-tours/payment/{{ $tour['id'] }}">
            @csrf

            <label>Payment Type</label>
            <select name="payment_type" id="paymentType">
                <option value="full">Full Payment (settle all balance)</option>
                <option value="partial">Partial Payment</option>
            </select>

            <label>Cash Received (₱)</label>
            <input type="number" name="cash_received" id="cashReceived"
                   min="0" step="0.01"
                   value="{{ $tour['balance_amount'] ?? 0 }}"
                   required>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px;">
                <div style="background:#f0fdf4;padding:14px;border-radius:10px;text-align:center;">
                    <p style="font-size:12px;color:#666;margin-bottom:4px;">Change</p>
                    <p id="changeDisplay" style="font-size:22px;font-weight:700;color:#1a6b3c;">₱0.00</p>
                </div>
                <div style="background:#fff7ed;padding:14px;border-radius:10px;text-align:center;">
                    <p style="font-size:12px;color:#666;margin-bottom:4px;">Remaining Balance</p>
                    <p id="balanceDisplay" style="font-size:22px;font-weight:700;color:#c0392b;">
                        ₱{{ number_format($tour['balance_amount'] ?? 0, 2) }}
                    </p>
                </div>
            </div>

            <button type="submit" class="btn btn-success"
                    style="width:100%;padding:13px;font-size:15px;margin-top:18px;">
                ✅ Confirm Payment
            </button>

        </form>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const cashInput   = document.getElementById('cashReceived');
    const payType     = document.getElementById('paymentType');
    const changeDisp  = document.getElementById('changeDisplay');
    const balDisp     = document.getElementById('balanceDisplay');

    const dbBalance = {{ $tour['balance_amount'] ?? 0 }};

    function fmt(n) {
        return '₱' + Math.abs(n).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    }

    function calculate() {
        const cash = parseFloat(cashInput.value) || 0;
        const change  = cash > dbBalance ? cash - dbBalance : 0;
        const balance = cash < dbBalance ? dbBalance - cash : 0;
        changeDisp.innerText = fmt(change);
        balDisp.innerText    = fmt(balance);
    }

    payType.addEventListener('change', function () {
        if (this.value === 'full') {
            cashInput.value = dbBalance.toFixed(2);
            calculate();
        }
    });

    cashInput.addEventListener('input', calculate);
    calculate();
});
</script>

@endsection
