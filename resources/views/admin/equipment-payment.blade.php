@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>💳 Equipment Payment</h2>
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

    {{-- PAYMENT FORM --}}
    <div class="card">
        <h3>Payment Details</h3>

        <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:16px;">
            <p style="margin:0;font-size:12px;"><strong>Guest:</strong> {{ $rental['guest_name'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Phone:</strong> {{ $rental['phone'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Total Amount:</strong> ₱{{ number_format($rental['total_amount'], 2) }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Already Paid:</strong> ₱{{ number_format($rental['paid_amount'] ?? 0, 2) }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Balance Due:</strong> ₱{{ number_format($rental['balance_amount'] ?? 0, 2) }}</p>
        </div>

        <h4 style="margin:16px 0 12px 0;font-size:13px;">Rental Items</h4>
        
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
                    {{ $item['quantity'] }} unit(s) × {{ $item['days'] }} day(s) @ ₱{{ number_format($item['unit_price'], 2) }}/day
                </p>
                <p style="margin:4px 0 0 0;font-weight:600;color:#0a4a6e;">
                    Subtotal: ₱{{ number_format($item['subtotal'], 2) }}
                </p>
            </div>
            @endforeach
        @endif

        <hr style="margin:16px 0;border:none;border-top:1px solid #ddd;">

        <form method="POST" style="display:grid;gap:14px;">
            @csrf

            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600;">Cash Received (₱) *</label>
                <input type="number" 
                       name="cash_received" 
                       step="0.01" 
                       min="0"
                       placeholder="0.00"
                       required
                       style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
                <p style="font-size:11px;color:#666;margin:4px 0 0 0;">
                    Balance due: ₱{{ number_format($rental['balance_amount'] ?? 0, 2) }}
                </p>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-weight:600;">
                Record Full Payment
            </button>
        </form>
    </div>

    {{-- SUMMARY --}}
    <div class="card">
        <h3>Summary</h3>

        <div style="background:#e8f4f8;padding:14px;border-radius:8px;border-left:4px solid #0a4a6e;margin-bottom:16px;text-align:center;">
            <p style="font-size:12px;color:#666;margin:0;text-transform:uppercase;letter-spacing:1px;">Amount Due</p>
            <p style="font-size:28px;font-weight:700;color:#0a4a6e;margin:6px 0 0 0;">
                ₱{{ number_format($rental['balance_amount'] ?? 0, 2) }}
            </p>
        </div>

        <div style="background:#fff3cd;padding:12px;border-radius:8px;border-left:4px solid #f39c12;font-size:12px;margin-bottom:16px;">
            <p style="margin:0;"><strong>Rental Status:</strong> {{ ucfirst($rental['status'] ?? 'unknown') }}</p>
            <p style="margin:4px 0 0 0;"><strong>Payment Status:</strong> {{ ucfirst($rental['payment_status'] ?? 'unpaid') }}</p>
        </div>

        <div style="background:#f0f0f0;padding:12px;border-radius:8px;font-size:12px;">
            <p style="margin:0;"><strong>Total:</strong> ₱{{ number_format($rental['total_amount'], 2) }}</p>
            <p style="margin:4px 0 0 0;"><strong>Paid:</strong> ₱{{ number_format($rental['paid_amount'] ?? 0, 2) }}</p>
            <p style="margin:4px 0 0 0;"><strong>Remaining:</strong> ₱{{ number_format($rental['balance_amount'] ?? 0, 2) }}</p>
        </div>

        <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-top:16px;font-size:12px;border-left:4px solid #666;">
            <p style="margin:0;"><strong>Rental Period:</strong></p>
            <p style="margin:4px 0 0 0;color:#666;">
                {{ date('M d, Y', strtotime($rental['rental_date'])) }} → {{ date('M d, Y', strtotime($rental['return_date'])) }}
            </p>
        </div>
    </div>

</div>

@endsection
