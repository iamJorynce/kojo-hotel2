@extends('admin.layout')

@section('content')

<div class="card">
    <h2>➕ Walk-in Booking</h2>

    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="/admin/bookings/create">
        @csrf

        {{-- GUEST INFO --}}
        <label>Guest Name</label>
        <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Full Name" required>

        <label>Phone Number</label>
        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Phone" required>

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email (optional)">

        {{-- ROOM SELECT --}}
        <label>Select Room</label>
        <select name="room_id" id="roomSelect" required>
            <option value="">-- Select Room --</option>
            @foreach($rooms as $room)
                @php
                    $category = collect($categories)->firstWhere('id', $room['category_id']);
                @endphp
                <option
                    value="{{ $room['uuid_id'] }}"
                    data-price="{{ $category['price'] ?? 0 }}"
                    {{ old('room_id') == $room['uuid_id'] ? 'selected' : '' }}>
                    Room {{ $room['room_number'] }} — {{ $room['name'] }} (₱{{ number_format($category['price'] ?? 0, 2) }})
                </option>
            @endforeach
        </select>

        {{-- DATES --}}
        <label>Check-in Date</label>
        <input type="date" name="check_in" id="check_in" required>

        <label>Check-out Date</label>
        <input type="date" name="check_out" id="check_out" required>

        {{-- DATE ERROR --}}
        <div id="date-error"
             style="display:none;background:#fee2e2;color:#991b1b;padding:10px;border-radius:6px;margin-bottom:10px;">
            ⚠️ Check-out must be after check-in.
        </div>

        {{-- BOOKING CALCULATOR --}}
        <div style="background:#0f172a;color:white;padding:15px;border-radius:12px;margin-top:10px;">
            <h3 style="margin-bottom:12px;">🧮 Booking Calculator</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <label style="color:#94a3b8;">Price per Night</label>
                    <input type="text" id="price" readonly
                           style="background:#1e293b;color:white;border-color:#334155;">
                </div>
                <div>
                    <label style="color:#94a3b8;">No. of Nights</label>
                    <input type="text" id="nights" readonly
                           style="background:#1e293b;color:white;border-color:#334155;">
                </div>
                <div>
                    <label style="color:#94a3b8;">Total Amount</label>
                    <input type="text" id="total" readonly
                           style="background:#1e293b;color:white;border-color:#334155;">
                </div>
                <div>
                    <label style="color:#94a3b8;">Downpayment (50%)</label>
                    <input type="text" id="dp" readonly
                           style="background:#1e293b;color:white;border-color:#334155;">
                </div>
            </div>
        </div>

        {{-- CASHIER SUMMARY --}}
        <div style="background:#111827;color:white;padding:20px;border-radius:12px;margin-top:15px;">
            <h3 style="margin-bottom:15px;">💳 Cashier Summary</h3>

            <label style="color:#94a3b8;">Payment Type</label>
            <select name="payment_type" id="payment_type" required
                    style="background:#1e293b;color:white;border-color:#334155;">
                <option value="full">Full Payment</option>
                <option value="partial">Partial Payment</option>
            </select>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:15px;text-align:center;margin-top:10px;">

                <div style="background:#1f2937;padding:15px;border-radius:10px;">
                    <small style="color:#94a3b8;">Total Amount</small>
                    <h2 id="total_amount_display" style="margin:5px 0;">0.00</h2>
                </div>

                <div style="background:#1f2937;padding:15px;border-radius:10px;">
                    <small style="color:#94a3b8;">Cash Received</small>
                    <input type="number" id="cash_received" name="cash_received"
                           value="{{ old('cash_received') }}"
                           min="0" step="0.01"
                           style="width:100%;padding:8px;margin-top:8px;text-align:center;font-size:18px;border-radius:6px;background:#374151;color:white;border:1px solid #4b5563;"
                           required>
                </div>

                <div style="background:#1f2937;padding:15px;border-radius:10px;">
                    <small style="color:#94a3b8;">Change</small>
                    <h2 id="change_display" style="color:#22c55e;margin:5px 0;">0.00</h2>
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-success"
                style="width:100%;margin-top:20px;padding:12px;font-size:15px;">
            ✅ Create Booking
        </button>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const today    = new Date().toISOString().split('T')[0];
    const checkIn  = document.getElementById('check_in');
    const checkOut = document.getElementById('check_out');

    checkIn.min  = today;
    checkOut.min = today;

    checkIn.addEventListener('change', function () {
        checkOut.min = this.value;
        // FIX: clear invalid checkout
        if (checkOut.value && checkOut.value <= this.value) {
            checkOut.value = '';
        }
        calculate();
    });

    checkOut.addEventListener('change', function () {
        if (this.value && checkIn.value && this.value <= checkIn.value) {
            document.getElementById('date-error').style.display = 'block';
            this.value = '';
        } else {
            document.getElementById('date-error').style.display = 'none';
            calculate();
        }
    });

    const roomSelect = document.getElementById('roomSelect');
    const priceEl    = document.getElementById('price');
    const nightsEl   = document.getElementById('nights');
    const totalEl    = document.getElementById('total');
    const dpEl       = document.getElementById('dp');
    const cash       = document.getElementById('cash_received');

    let roomPrice = 0;

    function calculate() {
        if (!checkIn.value || !checkOut.value || roomPrice <= 0) return;

        const diff        = new Date(checkOut.value) - new Date(checkIn.value);
        const nightsCount = diff / (1000 * 60 * 60 * 24);

        if (nightsCount <= 0) return;

        const totalAmount = nightsCount * roomPrice;
        const downpayment = totalAmount * 0.5;

        priceEl.value  = roomPrice.toFixed(2);
        nightsEl.value = nightsCount;
        totalEl.value  = totalAmount.toFixed(2);
        dpEl.value     = downpayment.toFixed(2);

        document.getElementById('total_amount_display').innerText = totalAmount.toFixed(2);
    }

    roomSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        roomPrice = Number(selected.dataset.price || 0);

        if (!roomPrice || roomPrice <= 0) {
            alert('Invalid room price. Please select a valid room.');
            this.value = '';
            return;
        }
        calculate();
    });

    cash.addEventListener('input', function () {
        const totalVal = Number(totalEl.value || 0);
        const cashVal  = Number(this.value     || 0);
        const change   = cashVal - totalVal;

        document.getElementById('change_display').innerText =
            change >= 0 ? change.toFixed(2) : '0.00';
    });

});
</script>

@endsection
