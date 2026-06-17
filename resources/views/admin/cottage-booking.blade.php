@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>🏠 New Cottage Booking</h2>
    <a href="/admin/cottage/bookings" class="btn">← Back to Bookings</a>
</div>

@if(session('error'))
    <div class="alert-error">❌ {{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">
        @foreach($errors->all() as $error)
            ❌ {{ $error }}<br>
        @endforeach
    </div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    {{-- DATE & GUEST INFO --}}
    <form method="POST" class="card" style="display:grid;gap:16px;" id="bookingForm">
        @csrf

        <h3>Guest Information</h3>

        <div>
            <label>Guest Name *</label>
            <input type="text" name="guest_name" required placeholder="Full name" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
        </div>

        <div>
            <label>Phone *</label>
            <input type="tel" name="guest_phone" required placeholder="09xxxxxxxxx" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="guest_email" placeholder="email@example.com" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid #ddd;">

        <h3>Booking Dates</h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <label>Check-In *</label>
                <input type="date" name="check_in" required id="checkInInput" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
            <div>
                <label>Check-Out *</label>
                <input type="date" name="check_out" required id="checkOutInput" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
        </div>

        <button type="button" class="btn btn-primary" style="width:100%;padding:12px;" onclick="searchAvailable()">
            🔍 Search Available Cottages
        </button>

        <hr style="margin:20px 0;border:none;border-top:1px solid #ddd;">

        <h3>Select Cottage</h3>

        <div id="cottageList" style="display:grid;gap:12px;">
            <p style="color:#999;text-align:center;">Select dates above to see available cottages</p>
        </div>

        <input type="hidden" name="cottage_id" id="selectedCottage" value="">

        <div>
            <label>Notes</label>
            <textarea name="notes" rows="2" placeholder="Special requests..." style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-weight:600;" id="submitBtn" disabled>
            ✓ Create Booking
        </button>
    </form>

    {{-- SUMMARY --}}
    <div class="card">
        <h3>Booking Summary</h3>

        <div id="summary" style="display:none;">
            <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:12px;">
                <p style="margin:0;font-size:12px;"><strong>Check-In:</strong></p>
                <p style="margin:4px 0 0 0;font-size:14px;font-weight:600;" id="summaryCheckIn">-</p>
            </div>

            <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:12px;">
                <p style="margin:0;font-size:12px;"><strong>Check-Out:</strong></p>
                <p style="margin:4px 0 0 0;font-size:14px;font-weight:600;" id="summaryCheckOut">-</p>
            </div>

            <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:12px;">
                <p style="margin:0;font-size:12px;"><strong>Nights:</strong></p>
                <p style="margin:4px 0 0 0;font-size:14px;font-weight:600;" id="summaryNights">-</p>
            </div>

            <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:12px;">
                <p style="margin:0;font-size:12px;"><strong>Selected Cottage:</strong></p>
                <p style="margin:4px 0 0 0;font-size:14px;font-weight:600;" id="summaryCottage">None</p>
            </div>

            <div style="background:#e8f4f8;padding:14px;border-radius:8px;border-left:4px solid #0a4a6e;text-align:center;">
                <p style="font-size:12px;color:#666;margin:0;">Total Cost</p>
                <p style="font-size:28px;font-weight:700;color:#0a4a6e;margin:6px 0 0 0;">
                    ₱<span id="summaryTotal">0.00</span>
                </p>
            </div>
        </div>

        <div id="noSelection" style="text-align:center;padding:20px;color:#999;">
            Select dates and a cottage to see summary
        </div>
    </div>

</div>

<script>
function searchAvailable() {
    const checkIn = document.getElementById('checkInInput').value;
    const checkOut = document.getElementById('checkOutInput').value;

    if (!checkIn || !checkOut) {
        alert('Please select both check-in and check-out dates');
        return;
    }

    // Calculate nights
    const start = new Date(checkIn);
    const end = new Date(checkOut);
    const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

    if (nights <= 0) {
        alert('Check-out must be after check-in');
        return;
    }

    // Fetch available cottages
    fetch(`/api/available-cottages?check_in=${checkIn}&check_out=${checkOut}`)
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('cottageList');
            list.innerHTML = '';

            if (data.available.length === 0) {
                list.innerHTML = '<p style="color:#c0392b;text-align:center;">❌ No cottages available for these dates</p>';
                return;
            }

            data.available.forEach(cottage => {
                const total = (parseFloat(cottage.price_per_day) * nights).toFixed(2);
                const html = `
                    <div style="background:#f0f0f0;padding:12px;border-radius:8px;cursor:pointer;border:2px solid transparent;transition:all 0.2s;" 
                         onclick="selectCottage(${cottage.id}, '${cottage.name}', ${total})" 
                         class="cottage-option"
                         data-id="${cottage.id}">
                        <p style="margin:0;font-weight:600;font-size:14px;">${cottage.name}</p>
                        <p style="margin:4px 0 0 0;font-size:12px;color:#666;">₱${parseFloat(cottage.price_per_day).toLocaleString('en-PH', {minimumFractionDigits: 2})} per night</p>
                        <p style="margin:4px 0 0 0;font-size:12px;color:#0a4a6e;font-weight:600;">${nights} night(s) = ₱${total}</p>
                    </div>
                `;
                list.innerHTML += html;
            });

            // Show summary
            document.getElementById('summary').style.display = 'block';
            document.getElementById('noSelection').style.display = 'none';
            document.getElementById('summaryCheckIn').textContent = new Date(checkIn).toLocaleDateString('en-PH');
            document.getElementById('summaryCheckOut').textContent = new Date(checkOut).toLocaleDateString('en-PH');
            document.getElementById('summaryNights').textContent = nights + ' night' + (nights > 1 ? 's' : '');
        })
        .catch(e => alert('Error fetching cottages: ' + e.message));
}

function selectCottage(id, name, total) {
    document.getElementById('selectedCottage').value = id;
    document.getElementById('summaryCottage').textContent = name;
    document.getElementById('summaryTotal').textContent = parseFloat(total).toFixed(2);
    document.getElementById('submitBtn').disabled = false;

    // Highlight selected
    document.querySelectorAll('.cottage-option').forEach(el => {
        el.style.borderColor = 'transparent';
        el.style.background = '#f0f0f0';
    });
    event.currentTarget.style.borderColor = '#0a4a6e';
    event.currentTarget.style.background = '#e8f4f8';
}
</script>

@endsection
