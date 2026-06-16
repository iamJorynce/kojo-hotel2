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
    <h2>💰 Payment Records</h2>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

{{-- STATS --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px;">

    <div class="card" style="text-align:center;border-top:4px solid #0a4a6e;">
        <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Total Received</p>
        <p style="font-size:28px;font-weight:700;color:#0a4a6e;">₱{{ number_format($totalReceived, 2) }}</p>
    </div>

    <div class="card" style="text-align:center;border-top:4px solid #1a6b3c;">
        <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Room Bookings</p>
        <p style="font-size:28px;font-weight:700;color:#1a6b3c;">₱{{ number_format($bookingPayments, 2) }}</p>
    </div>

    <div class="card" style="text-align:center;border-top:4px solid #854d0e;">
        <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Day Tours</p>
        <p style="font-size:28px;font-weight:700;color:#854d0e;">₱{{ number_format($dayTourPayments, 2) }}</p>
    </div>

    <div class="card" style="text-align:center;border-top:4px solid #6b21a8;">
        <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Transactions</p>
        <p style="font-size:28px;font-weight:700;color:#6b21a8;">{{ $payments->count() }}</p>
    </div>

</div>

{{-- PER STAFF SUMMARY --}}
@if($perStaff->count() > 0)
<div class="card" style="margin-bottom:20px;">
    <h3 style="margin-bottom:14px;">👤 Per Staff Summary</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
        @foreach($perStaff as $name => $summary)
        <div style="background:#f8fafc;padding:14px;border-radius:10px;border-left:4px solid #0a4a6e;">
            <p style="font-weight:600;margin-bottom:4px;">{{ $name }}</p>
            <p style="font-size:22px;font-weight:700;color:#1a6b3c;">₱{{ number_format($summary['total'], 2) }}</p>
            <p style="font-size:12px;color:#888;">{{ $summary['count'] }} transaction(s)</p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- FILTERS --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="/admin/payments"
          style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">

        <div style="flex:1;min-width:150px;">
            <label>Staff</label>
            <select name="staff_id">
                <option value="">All Staff</option>
                @foreach($staff as $s)
                    <option value="{{ $s['id'] }}" {{ ($filters['staff_id'] ?? '') == $s['id'] ? 'selected' : '' }}>
                        {{ $s['full_name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex:1;min-width:140px;">
            <label>Type</label>
            <select name="type">
                <option value="">All Types</option>
                <option value="booking"  {{ ($filters['target_type'] ?? '') === 'booking'  ? 'selected' : '' }}>Room Booking</option>
                <option value="day_tour" {{ ($filters['target_type'] ?? '') === 'day_tour' ? 'selected' : '' }}>Day Tour</option>
            </select>
        </div>

        <div style="flex:1;min-width:140px;">
            <label>Date</label>
            <input type="date" name="date" value="{{ $filters['date'] ?? date('Y-m-d') }}">
        </div>

        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary" style="padding:10px 16px;">Filter</button>
            <a href="/admin/payments" class="btn" style="padding:10px 16px;">Clear</a>
        </div>

    </form>
</div>

{{-- TABLE --}}
@if($payments->isEmpty())
    <div class="card" style="text-align:center;padding:40px;color:#999;">
        No payment records found.
    </div>
@else
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);font-size:13px;">
    <thead>
        <tr style="background:#0f172a;color:white;">
            <th style="padding:11px 14px;text-align:left;">Date & Time</th>
            <th style="padding:11px 14px;text-align:left;">Received By</th>
            <th style="padding:11px 14px;text-align:left;">Guest</th>
            <th style="padding:11px 14px;text-align:left;">Details</th>
            <th style="padding:11px 14px;text-align:center;">Type</th>
            <th style="padding:11px 14px;text-align:center;">Payment</th>
            <th style="padding:11px 14px;text-align:right;">Amount</th>
            <th style="padding:11px 14px;text-align:right;">Balance After</th>
        </tr>
    </thead>
    <tbody>
    @foreach($payments as $p)
    <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:10px 14px;white-space:nowrap;color:#666;">
            {{ \Carbon\Carbon::parse($p['received_at'])->format('M d, Y') }}<br>
            <span style="font-size:11px;">{{ \Carbon\Carbon::parse($p['received_at'])->format('h:i A') }}</span>
        </td>
        <td style="padding:10px 14px;font-weight:600;">{{ $p['staff_name'] ?? '-' }}</td>
        <td style="padding:10px 14px;">{{ $p['guest_name'] ?? '-' }}</td>
        <td style="padding:10px 14px;color:#666;font-size:12px;">{{ $p['room_info'] ?? '-' }}</td>
        <td style="padding:10px 14px;text-align:center;">
            <span style="
                background:{{ $p['target_type'] === 'day_tour' ? '#854d0e' : '#0a4a6e' }};
                color:white;padding:3px 9px;border-radius:5px;font-size:11px;font-weight:500;">
                {{ $p['target_type'] === 'day_tour' ? '🏖 Day Tour' : '🏠 Booking' }}
            </span>
        </td>
        <td style="padding:10px 14px;text-align:center;">
            <span style="
                background:{{ $p['payment_type'] === 'full' ? '#1a6b3c' : '#854d0e' }};
                color:white;padding:3px 9px;border-radius:5px;font-size:11px;font-weight:500;">
                {{ ucfirst($p['payment_type'] ?? '-') }}
            </span>
        </td>
        <td style="padding:10px 14px;text-align:right;font-weight:700;color:#1a6b3c;font-size:15px;">
            ₱{{ number_format($p['amount_received'], 2) }}
        </td>
        <td style="padding:10px 14px;text-align:right;color:{{ ($p['balance_after'] ?? 0) > 0 ? '#c0392b' : '#1a6b3c' }};font-weight:600;">
            ₱{{ number_format($p['balance_after'] ?? 0, 2) }}
        </td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr style="background:#f8fafc;font-weight:700;font-size:14px;">
            <td colspan="6" style="padding:12px 14px;text-align:right;color:#555;">Total Received:</td>
            <td style="padding:12px 14px;text-align:right;color:#1a6b3c;font-size:16px;">
                ₱{{ number_format($totalReceived, 2) }}
            </td>
            <td></td>
        </tr>
    </tfoot>
</table>
</div>
@endif

@endsection
