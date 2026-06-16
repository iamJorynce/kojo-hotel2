@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>🏖 Walk-in Day Tour</h2>
    <a href="/admin/day-tours" class="btn">← Back</a>
</div>

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:24px;max-width:1000px;">

    {{-- FORM --}}
    <div class="card">
        <h3 style="margin-bottom:18px;">Guest Information</h3>

        <form method="POST" action="/admin/day-tours/walkin" id="walkinForm">
            @csrf

            <label>Full Name</label>
            <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Guest name" required>

            <label>Phone Number</label>
            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="09XX XXX XXXX" required>

            <label>Email (optional)</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com">

            <label>Package</label>
            <select name="package_id" id="packageSelect" required>
                <option value="">-- Select Package --</option>
                @foreach($packages as $pkg)
                    <option value="{{ $pkg['id'] }}"
                            data-price="{{ $pkg['price_per_person'] }}"
                            data-name="{{ $pkg['name'] }}"
                            {{ old('package_id') == $pkg['id'] ? 'selected' : '' }}>
                        {{ $pkg['name'] }} — ₱{{ number_format($pkg['price_per_person'], 2) }}/person
                    </option>
                @endforeach
            </select>

            <label>Number of Guests</label>
            <input type="number" name="guest_count" id="guestCount"
                   min="1" max="500"
                   value="{{ old('guest_count', 1) }}" required>

            <label>Notes (optional)</label>
            <textarea name="notes" rows="2" placeholder="Any notes...">{{ old('notes') }}</textarea>

            <hr style="margin:20px 0;border:none;border-top:1px solid #eee;">

            <h3 style="margin-bottom:14px;">💳 Payment</h3>

            <label>Payment Type</label>
            <select name="payment_type" id="paymentType">
                <option value="full">Full Payment</option>
                <option value="partial">Partial Payment</option>
                <option value="none">No Payment Now</option>
            </select>

            <label>Cash Received (₱)</label>
            <input type="number" name="cash_received" id="cashReceived"
                   min="0" step="0.01" value="{{ old('cash_received', 0) }}">

            <button type="submit" class="btn btn-success"
                    style="width:100%;padding:12px;font-size:15px;margin-top:14px;">
                ✅ Create Walk-in Booking
            </button>

        </form>
    </div>

    {{-- CASHIER SUMMARY --}}
    <div>
        <div class="card" style="background:#0f172a;color:white;position:sticky;top:20px;">
            <h3 style="color:#38bdf8;margin-bottom:18px;">🧮 Cashier Summary</h3>

            <div style="margin-bottom:12px;">
                <p style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">Package</p>
                <p id="s-package" style="font-size:16px;font-weight:600;">—</p>
            </div>
            <div style="margin-bottom:12px;">
                <p style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">Price / Person</p>
                <p id="s-price" style="font-size:16px;font-weight:600;">₱0.00</p>
            </div>
            <div style="margin-bottom:12px;">
                <p style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">Guests</p>
                <p id="s-guests" style="font-size:16px;font-weight:600;">0</p>
            </div>

            <hr style="border-color:rgba(255,255,255,0.1);margin:14px 0;">

            <div style="margin-bottom:12px;">
                <p style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">Total Amount</p>
                <p id="s-total" style="font-size:28px;font-weight:700;color:#f0d49a;">₱0.00</p>
            </div>
            <div style="margin-bottom:12px;">
                <p style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">Cash Received</p>
                <p id="s-cash" style="font-size:20px;font-weight:600;color:#86efac;">₱0.00</p>
            </div>
            <div style="margin-bottom:12px;">
                <p style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">Change</p>
                <p id="s-change" style="font-size:20px;font-weight:600;color:#67e8f9;">₱0.00</p>
            </div>
            <div>
                <p style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">Balance</p>
                <p id="s-balance" style="font-size:20px;font-weight:600;color:#fca5a5;">₱0.00</p>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const pkgSelect    = document.getElementById('packageSelect');
    const guestInput   = document.getElementById('guestCount');
    const cashInput    = document.getElementById('cashReceived');
    const payTypeInput = document.getElementById('paymentType');

    let pricePerPerson = 0;

    function fmt(n) {
        return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    }

    function calculate() {
        const guests = parseInt(guestInput.value) || 0;
        const total  = pricePerPerson * guests;
        const cash   = parseFloat(cashInput.value) || 0;
        const change = cash > total ? cash - total : 0;
        const bal    = total > cash ? total - cash : 0;

        document.getElementById('s-guests').innerText  = guests;
        document.getElementById('s-total').innerText   = fmt(total);
        document.getElementById('s-cash').innerText    = fmt(cash);
        document.getElementById('s-change').innerText  = fmt(change);
        document.getElementById('s-balance').innerText = fmt(bal);
    }

    pkgSelect.addEventListener('change', function () {
        const opt      = this.options[this.selectedIndex];
        pricePerPerson = parseFloat(opt.dataset.price) || 0;
        document.getElementById('s-package').innerText = opt.dataset.name || '—';
        document.getElementById('s-price').innerText   = fmt(pricePerPerson);
        calculate();
    });

    guestInput.addEventListener('input', calculate);
    cashInput.addEventListener('input', calculate);

    // Auto-fill cash for full payment
    payTypeInput.addEventListener('change', function () {
        if (this.value === 'full') {
            const guests = parseInt(guestInput.value) || 0;
            cashInput.value = (pricePerPerson * guests).toFixed(2);
        } else if (this.value === 'none') {
            cashInput.value = 0;
        }
        calculate();
    });
});
</script>

@endsection
