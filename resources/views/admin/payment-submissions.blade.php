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
    <h2>📋 Payment Submissions</h2>
    <a href="/admin/remittance-report" class="btn">📊 Daily Report</a>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

{{-- FILTERS --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:120px;">
            <label>Status</label>
            <select name="status">
                <option value="pending" {{ ($status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="">All</option>
            </select>
        </div>
        <div style="flex:1;min-width:120px;">
            <label>Date</label>
            <input type="date" name="date" value="{{ $date ?? '' }}">
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/admin/payment-submissions" class="btn">Clear</a>
        </div>
    </form>
</div>

{{-- TABLE --}}
@if($submissions->isEmpty())
    <div class="card" style="text-align:center;padding:40px;color:#999;">
        No payment submissions found.
    </div>
@else
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);font-size:13px;">
    <thead>
        <tr style="background:#0f172a;color:white;">
            <th style="padding:11px 14px;text-align:left;">Submitted By</th>
            <th style="padding:11px 14px;text-align:center;">Date</th>
            <th style="padding:11px 14px;text-align:right;">Total Cash</th>
            <th style="padding:11px 14px;text-align:center;">Transactions</th>
            <th style="padding:11px 14px;text-align:center;">Status</th>
            <th style="padding:11px 14px;text-align:center;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($submissions as $sub)
    <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:10px 14px;font-weight:600;">
            {{ $sub['staff_name'] ?? '-' }}<br>
            <span style="font-size:11px;color:#666;">{{ date('M d, h:i A', strtotime($sub['created_at'])) }}</span>
        </td>
        <td style="padding:10px 14px;text-align:center;color:#666;">{{ date('M d, Y', strtotime($sub['submission_date'])) }}</td>
        <td style="padding:10px 14px;text-align:right;font-weight:700;color:#1a6b3c;font-size:15px;">
            ₱{{ number_format($sub['total_cash'], 2) }}
        </td>
        <td style="padding:10px 14px;text-align:center;">{{ $sub['payment_count'] ?? 0 }}</td>
        <td style="padding:10px 14px;text-align:center;">
            <span style="background:{{ $sub['status'] === 'approved' ? '#1a6b3c' : ($sub['status'] === 'rejected' ? '#c0392b' : '#f39c12') }};color:white;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;">
                {{ strtoupper($sub['status']) }}
            </span>
        </td>
        <td style="padding:10px 14px;text-align:center;">
            <a href="/admin/payment-submission/{{ $sub['id'] }}" class="btn" style="padding:4px 8px;font-size:11px;">View</a>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

@endsection
