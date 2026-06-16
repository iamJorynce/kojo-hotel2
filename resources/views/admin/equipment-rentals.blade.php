@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>📋 Equipment Rentals</h2>
    <a href="/admin/equipment/walkin" class="btn">⛱️ New Rental</a>
</div>

@if(session('success'))
    <div class="alert-success">✅ {{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">❌ {{ session('error') }}</div>
@endif

{{-- FILTERS --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:120px;">
            <label>Status</label>
            <select name="status">
                <option value="">All</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div style="flex:1;min-width:120px;">
            <label>Payment Status</label>
            <select name="payment_status">
                <option value="">All</option>
                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/admin/equipment/rentals" class="btn">Clear</a>
        </div>
    </form>
</div>

{{-- TABLE --}}
@if(empty($rentals))
    <div class="card" style="text-align:center;padding:40px;color:#999;">
        No equipment rentals found.
    </div>
@else
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);font-size:13px;">
    <thead>
        <tr style="background:#0f172a;color:white;">
            <th style="padding:11px 14px;text-align:left;">Guest</th>
            <th style="padding:11px 14px;text-align:left;">Items Rented</th>
            <th style="padding:11px 14px;text-align:center;">Dates</th>
            <th style="padding:11px 14px;text-align:right;">Amount</th>
            <th style="padding:11px 14px;text-align:center;">Status</th>
            <th style="padding:11px 14px;text-align:center;">Payment</th>
            <th style="padding:11px 14px;text-align:center;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($rentals as $rental)
    <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:10px 14px;font-weight:600;">
            {{ $rental['guest_name'] ?? '-' }}<br>
            <span style="font-size:11px;color:#666;">{{ $rental['phone'] ?? '-' }}</span>
        </td>
        
        {{-- ITEMS COLUMN --}}
        <td style="padding:10px 14px;">
            @if(empty($rental['items']))
                <span style="color:#999;font-size:11px;">No items</span>
            @else
                @foreach($rental['items'] as $item)
                <div style="margin:4px 0;padding:6px 8px;background:#f0f0f0;border-radius:4px;font-size:11px;">
                    <strong>{{ $item['item_name'] ?? 'Item' }}</strong><br>
                    <span style="color:#666;">{{ $item['quantity'] }} × {{ $item['days'] }} day(s) = ₱{{ number_format($item['subtotal'], 2) }}</span>
                </div>
                @endforeach
            @endif
        </td>
        
        <td style="padding:10px 14px;text-align:center;font-size:12px;">
            {{ date('M d', strtotime($rental['rental_date'])) }} → {{ date('M d', strtotime($rental['return_date'])) }}
        </td>
        <td style="padding:10px 14px;text-align:right;font-weight:600;">₱{{ number_format($rental['total_amount'], 2) }}</td>
        
        <td style="padding:10px 14px;text-align:center;">
            @if($rental['status'] === 'returned')
                <span style="background:#1a6b3c;color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                    ✓ RETURNED
                </span>
            @elseif($rental['status'] === 'cancelled')
                <span style="background:#c0392b;color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                    ✗ CANCELLED
                </span>
            @else
                <span style="background:#f39c12;color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                    ACTIVE
                </span>
            @endif
        </td>
        
        <td style="padding:10px 14px;text-align:center;">
            <span style="background:{{ $rental['payment_status'] === 'paid' ? '#1a6b3c' : '#c0392b' }};color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                {{ strtoupper($rental['payment_status']) }}
            </span>
        </td>
        
        <td style="padding:10px 14px;text-align:center;">
            {{-- LOGIC: ACTIVE + UNPAID = PAY ONLY, ACTIVE + PAID = RETURN ONLY --}}
            @if($rental['status'] === 'active')
                @if($rental['payment_status'] !== 'paid')
                    {{-- UNPAID - SHOW PAY ONLY --}}
                    <a href="/admin/equipment/payment/{{ $rental['id'] }}" class="btn" style="padding:4px 8px;font-size:11px;margin:2px;background:#0a4a6e;color:white;display:inline-block;text-decoration:none;border-radius:4px;">💳 Pay</a>
                    <span style="font-size:10px;color:#c0392b;display:block;margin-top:4px;"><strong>Pay first to return</strong></span>
                @else
                    {{-- PAID - SHOW RETURN ONLY --}}
                    <a href="/admin/equipment/return/{{ $rental['id'] }}" class="btn" style="padding:4px 8px;font-size:11px;margin:2px;background:#1a6b3c;color:white;display:inline-block;text-decoration:none;border-radius:4px;">📋 Return</a>
                @endif
            @else
                {{-- RETURNED OR CANCELLED - NO BUTTONS --}}
                <span style="font-size:11px;color:#999;text-align:center;">No actions</span>
            @endif
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

@endsection
