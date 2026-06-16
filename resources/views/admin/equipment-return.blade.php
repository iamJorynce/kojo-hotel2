@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>📋 Equipment Return Inspection</h2>
    <a href="/admin/equipment/rentals" class="btn">← Back</a>
</div>

@if(session('success'))
    <div class="alert-success">✅ {{ session('success') }}</div>
@endif

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

    {{-- RETURN FORM --}}
    <div class="card">
        <h3>Return Inspection</h3>

        <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:16px;">
            <p style="margin:0;font-size:12px;"><strong>Guest:</strong> {{ $rental['guest_name'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Phone:</strong> {{ $rental['phone'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Rental Period:</strong> {{ date('M d', strtotime($rental['rental_date'])) }} → {{ date('M d', strtotime($rental['return_date'])) }}</p>
        </div>

        <h4 style="margin:16px 0 12px 0;font-size:13px;">Items Rented</h4>
        
        @php
            $items = $supabase->getRentalItems($rental['id']);
        @endphp

        @if(empty($items))
            <div style="background:#f0f0f0;padding:12px;border-radius:6px;color:#999;font-size:12px;">
                No items in this rental
            </div>
        @else
            @foreach($items as $item)
            <div style="background:#f0f0f0;padding:10px;margin-bottom:8px;border-radius:6px;border-left:4px solid #0a4a6e;">
                <p style="margin:0;font-weight:600;font-size:12px;">{{ $item['item_name'] ?? 'Item' }}</p>
                <p style="margin:4px 0 0 0;font-size:11px;color:#666;">
                    {{ $item['quantity'] }} unit(s) × {{ $item['days'] }} day(s)
                </p>
            </div>
            @endforeach
        @endif

        <hr style="margin:16px 0;border:none;border-top:1px solid #ddd;">

        <form method="POST" style="display:grid;gap:14px;">
            @csrf

            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600;">Return Date</label>
                <input type="date" 
                       name="returned_date" 
                       value="{{ date('Y-m-d') }}"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>

            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600;">Return Time</label>
                <input type="time" 
                       name="returned_time" 
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>

            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600;">Condition *</label>
                <select name="condition" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                    <option value="">Select...</option>
                    <option value="good">✓ Good (No damage)</option>
                    <option value="damaged">⚠️ Damaged (Charge applicable)</option>
                    <option value="missing">✗ Missing (Full charge)</option>
                </select>
            </div>

            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600;">Damage Description</label>
                <textarea name="damage_description" 
                          rows="3"
                          placeholder="Describe any damage..."
                          style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"></textarea>
            </div>

            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600;">Damage Charge (₱)</label>
                <input type="number" 
                       name="damage_amount" 
                       step="0.01" 
                       min="0"
                       value="0"
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                <p style="font-size:11px;color:#666;margin:4px 0 0 0;">
                    Enter 0 if no damage
                </p>
            </div>

            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600;">Notes</label>
                <textarea name="notes" 
                          rows="2"
                          placeholder="Additional notes..."
                          style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-weight:600;">
                ✓ Submit Return Inspection
            </button>
        </form>
    </div>

    {{-- SUMMARY --}}
    <div class="card">
        <h3>Summary</h3>

        <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:16px;">
            <p style="margin:0;font-size:12px;"><strong>Items:</strong> {{ count($items) }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Status:</strong> {{ ucfirst($rental['status']) }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Payment:</strong> {{ ucfirst($rental['payment_status']) }}</p>
        </div>

        @if($rental['return'])
        <div style="background:#e8f5e9;padding:12px;border-radius:8px;border-left:4px solid #1a6b3c;margin-bottom:16px;">
            <p style="margin:0;font-weight:600;font-size:12px;color:#1a6b3c;">Already Returned</p>
            <p style="margin:4px 0 0 0;font-size:11px;color:#666;">
                Condition: {{ ucfirst($rental['return']['condition'] ?? 'unknown') }}<br>
                Damage charge: ₱{{ number_format($rental['return']['damage_amount'] ?? 0, 2) }}
            </p>
        </div>
        @endif

        <div style="background:#fff3cd;padding:12px;border-radius:8px;border-left:4px solid #f39c12;font-size:12px;">
            <p style="margin:0;"><strong>Amount Due:</strong> ₱{{ number_format($rental['balance_amount'] ?? 0, 2) }}</p>
            <p style="margin:4px 0 0 0;"><strong>Total:</strong> ₱{{ number_format($rental['total_amount'], 2) }}</p>
        </div>
    </div>

</div>

@endsection
