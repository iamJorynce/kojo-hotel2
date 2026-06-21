@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>📋 Day Tour Transactions</h2>
    <a href="/admin/walkin/daytour" class="btn">+ New Transaction</a>
</div>

@if(session('success'))
    <div class="alert-success">✅ {{ session('success') }}</div>
@endif

{{-- FILTERS --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:120px;">
            <label>Filter</label>
            <select name="filter">
                <option value="recent" {{ ($filter ?? '') === 'recent' ? 'selected' : '' }}>Recent</option>
                <option value="unpaid" {{ ($filter ?? '') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/admin/walkin/daytours" class="btn">Clear</a>
        </div>
    </form>
</div>

{{-- TABLE --}}
@if(empty($tours))
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
            <th style="padding:11px 14px;text-align:right;">Amount</th>
            <th style="padding:11px 14px;text-align:center;">Payment</th>
            <th style="padding:11px 14px;text-align:center;">Date</th>
            <th style="padding:11px 14px;text-align:center;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($tours as $tour)
    <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:10px 14px;font-weight:600;font-size:12px;">
            {{ substr($tour['transaction_id'] ?? '', 0, 20) }}...
        </td>
        <td style="padding:10px 14px;">
            <strong>{{ $tour['guest_name'] ?? 'Unknown' }}</strong><br>
            <span style="font-size:11px;color:#666;">{{ $tour['guest_phone'] ?? '-' }}</span>
        </td>
        <td style="padding:10px 14px;text-align:right;font-weight:600;">
            ₱{{ number_format($tour['total_amount'] ?? 0, 2) }}
        </td>
        <td style="padding:10px 14px;text-align:center;">
            <span style="background:{{ ($tour['payment_status'] ?? '') === 'paid' ? '#1a6b3c' : '#c0392b' }};color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                {{ strtoupper($tour['payment_status'] ?? 'unpaid') }}
            </span>
        </td>
        <td style="padding:10px 14px;text-align:center;font-size:12px;">
            {{ date('M d, Y', strtotime($tour['created_at'] ?? 'now')) }}
        </td>
        <td style="padding:10px 14px;text-align:center;">
            <a href="/admin/walkin/daytour/{{ $tour['id'] }}/payment" class="btn" style="padding:4px 8px;font-size:11px;background:#0a4a6e;color:white;text-decoration:none;border-radius:4px;display:inline-block;">View</a>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

@endsection
