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
    <h2>⛱️ New Equipment Rental</h2>
    <a href="/admin/equipment/rentals" class="btn">← Back</a>
</div>

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    {{-- FORM --}}
    <form method="POST" class="card" style="display:grid;gap:16px;">
        @csrf

        <h3>Guest Information</h3>

        <div>
            <label>Guest Name *</label>
            <input type="text" name="guest_name" required placeholder="Full name" style="width:100%;padding:10px;">
        </div>

        <div>
            <label>Phone *</label>
            <input type="tel" name="phone" required placeholder="09xxxxxxxxx" style="width:100%;padding:10px;">
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" placeholder="email@example.com" style="width:100%;padding:10px;">
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid #ddd;">

        <h3>Rental Dates</h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <label>Rental Date *</label>
                <input type="date" name="rental_date" required style="width:100%;padding:10px;">
            </div>
            <div>
                <label>Return Date *</label>
                <input type="date" name="return_date" required style="width:100%;padding:10px;">
            </div>
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid #ddd;">

        <h3>Equipment</h3>

        @if(empty($equipmentTypes))
            <p style="color:#999;">No equipment available</p>
        @else
            @foreach($equipmentTypes as $eq)
            <div style="background:#f8fafc;padding:12px;border-radius:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <div>
                        <strong>{{ $eq['name'] ?? 'Equipment' }}</strong>
                        <p style="font-size:12px;color:#666;margin:2px 0 0 0;">
                            ₱{{ number_format($eq['unit_price'] ?? 0, 2) }}/day
                        </p>
                    </div>
                    <span style="background:#1a6b3c;color:white;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;">
                        Available: {{ $eq['quantity_available'] ?? 0 }}
                    </span>
                </div>
                <input 
                    type="number" 
                    name="equipment[{{ $eq['id'] }}]" 
                    min="0" 
                    value="0" 
                    placeholder="Quantity"
                    style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;"
                >
            </div>
            @endforeach
        @endif

   

        <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;margin-top:20px;">
            Create Rental
        </button>
    </form>

    {{-- SUMMARY --}}
    <div class="card">
        <h3>Summary</h3>

        <div id="summary" style="background:#f0f0f0;padding:16px;border-radius:8px;text-align:center;">
            <p style="font-size:12px;color:#666;margin:0;">Total Amount</p>
            <p style="font-size:32px;font-weight:700;color:#1a6b3c;margin:8px 0 0 0;">
                ₱<span id="totalAmount">0.00</span>
            </p>
        </div>

        <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-top:16px;font-size:13px;">
            <p style="margin:0;"><strong>Items:</strong> <span id="itemCount">0</span></p>
            <p style="margin:4px 0 0 0;"><strong>Days:</strong> <span id="dayCount">1</span></p>
        </div>
    </div>

</div>

<script>
function updateSummary() {
    const rentalDate = new Date(document.querySelector('input[name="rental_date"]').value);
    const returnDate = new Date(document.querySelector('input[name="return_date"]').value);
    
    let days = 1;
    if (rentalDate && returnDate && returnDate > rentalDate) {
        days = Math.ceil((returnDate - rentalDate) / (1000 * 60 * 60 * 24));
    }
    
    let total = 0;
    let itemCount = 0;

    // Equipment
    @foreach($equipmentTypes as $eq)
    const eq{{ $eq['id'] }} = document.querySelector('input[name="equipment[{{ $eq['id'] }}]"]');
    if (eq{{ $eq['id'] }}) {
        const qty = parseInt(eq{{ $eq['id'] }}.value) || 0;
        if (qty > 0) {
            total += qty * {{ $eq['unit_price'] ?? 0 }} * days;
            itemCount += qty;
        }
    }
    @endforeach

    // Cottages
    @foreach($cottages as $cot)
    const cot{{ $cot['id'] }} = document.querySelector('input[name="cottages[{{ $cot['id'] }}]"]');
    if (cot{{ $cot['id'] }}) {
        const qty = parseInt(cot{{ $cot['id'] }}.value) || 0;
        if (qty > 0) {
            total += qty * {{ $cot['price_per_day'] ?? 0 }} * days;
            itemCount += qty;
        }
    }
    @endforeach

    document.getElementById('totalAmount').textContent = total.toFixed(2);
    document.getElementById('itemCount').textContent = itemCount;
    document.getElementById('dayCount').textContent = days;
}

// Update on input change
document.querySelectorAll('input[type="number"], input[type="date"]').forEach(el => {
    el.addEventListener('change', updateSummary);
    el.addEventListener('input', updateSummary);
});

// Initial update
updateSummary();
</script>

@endsection
