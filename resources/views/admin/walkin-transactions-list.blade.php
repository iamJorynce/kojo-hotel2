@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>📋 Walk-In Transactions</h2>
    <a href="/admin/walkin/pos" class="btn">+ New Transaction</a>
</div>

{{-- FILTERS --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:120px;">
            <label>Type</label>
            <select name="type">
                <option value="">All Types</option>
                <option value="day_tour" {{ ($type ?? '') === 'day_tour' ? 'selected' : '' }}>Day Tour</option>
                <option value="booking" {{ ($type ?? '') === 'booking' ? 'selected' : '' }}>Booking</option>
                <option value="equipment" {{ ($type ?? '') === 'equipment' ? 'selected' : '' }}>Equipment</option>
            </select>
        </div>
        <div style="flex:1;min-width:120px;">
            <label>Payment Status</label>
            <select name="status">
                <option value="">All Status</option>
                <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="unpaid" {{ ($status ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="partial" {{ ($status ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/admin/walkin/transactions" class="btn">Clear</a>
        </div>
    </form>
</div>

{{-- TABLE --}}
@if(empty($transactions))
    <div class="card" style="text-align:center;padding:40px;color:#999;">
        No transactions found.
    </div>
@else
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);font-size:13px;">
    <thead>
        <tr style="background:#0f172a;color:white;">
            <th style="padding:11px 14px;text-align:left;">Transaction ID</th>
            <th style="padding:11px 14px;text-align:left;">Guest</th>
            <th style="padding:11px 14px;text-align:center;">Type</th>
            <th style="padding:11px 14px;text-align:right;">Amount</th>
            <th style="padding:11px 14px;text-align:center;">Payment</th>
            <th style="padding:11px 14px;text-align:center;">Status</th>
            <th style="padding:11px 14px;text-align:center;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($transactions as $tx)
    <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:10px 14px;font-weight:600;font-size:12px;">{{ substr($tx['transaction_id'], 0, 20) }}</td>
        <td style="padding:10px 14px;">
            <strong>{{ $tx['guest_name'] }}</strong><br>
            <span style="font-size:11px;color:#666;">{{ $tx['guest_phone'] }}</span>
        </td>
        <td style="padding:10px 14px;text-align:center;font-size:12px;">
            @if($tx['transaction_type'] === 'day_tour')
                🧾 Day Tour
            @elseif($tx['transaction_type'] === 'booking')
                🛏️ Booking
            @else
                🧰 Equipment
            @endif
        </td>
        <td style="padding:10px 14px;text-align:right;font-weight:600;">₱{{ number_format($tx['total_amount'], 2) }}</td>
        <td style="padding:10px 14px;text-align:center;">
            <span style="background:{{ ($tx['payment_status'] === 'paid') ? '#1a6b3c' : (($tx['payment_status'] === 'partial') ? '#f39c12' : '#c0392b') }};color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                {{ strtoupper($tx['payment_status']) }}
            </span>
        </td>
        <td style="padding:10px 14px;text-align:center;font-size:12px;">
            {{ strtoupper(str_replace('_', ' ', $tx['transaction_status'])) }}
        </td>
        <td style="padding:10px 14px;text-align:center;">
            <a href="/admin/walkin/payment/{{ $tx['transaction_id'] }}" class="btn" style="padding:4px 8px;font-size:11px;background:#0a4a6e;color:white;text-decoration:none;border-radius:4px;display:inline-block;">View</a>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

@endsection
