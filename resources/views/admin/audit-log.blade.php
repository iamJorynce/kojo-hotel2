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
    <h2>📋 Audit Log</h2>
</div>

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

{{-- FILTERS --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="/admin/audit-log"
          style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">

        <div style="flex:1;min-width:160px;">
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

        <div style="flex:1;min-width:160px;">
            <label>Action</label>
            <select name="action">
                <option value="">All Actions</option>
                @foreach([
                    'login'              => '🔑 Login',
                    'logout'             => '🚪 Logout',
                    'payment_received'   => '💳 Payment Received',
                    'booking_created'    => '📋 Booking Created',
                    'booking_confirmed'  => '✅ Booking Confirmed',
                    'booking_cancelled'  => '❌ Booking Cancelled',
                    'checkin'            => '🏨 Check-in',
                    'checkout'           => '🔴 Check-out',
                    'room_created'       => '🏠 Room Created',
                    'room_deleted'       => '🗑 Room Deleted',
                    'day_tour_created'   => '🏖 Day Tour Created',
                    'day_tour_payment'   => '💰 Day Tour Payment',
                    'staff_created'      => '👤 Staff Created',
                    'staff_updated'      => '✏️ Staff Updated',
                    'staff_deleted'      => '🗑 Staff Deleted',
                ] as $val => $lbl)
                    <option value="{{ $val }}" {{ ($filters['action'] ?? '') === $val ? 'selected' : '' }}>
                        {{ $lbl }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex:1;min-width:140px;">
            <label>Date</label>
            <input type="date" name="date" value="{{ $filters['date'] ?? '' }}">
        </div>

        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary" style="padding:10px 16px;">Filter</button>
            <a href="/admin/audit-log" class="btn" style="padding:10px 16px;">Clear</a>
        </div>

    </form>
</div>

{{-- LOG TABLE --}}
@if($logs->isEmpty())
    <div class="card" style="text-align:center;padding:40px;color:#999;">
        No logs found for the selected filters.
    </div>
@else

<div style="margin-bottom:10px;font-size:13px;color:#888;">
    Showing {{ $logs->count() }} records
</div>

<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);font-size:13px;">
    <thead>
        <tr style="background:#0f172a;color:white;">
            <th style="padding:11px 14px;text-align:left;">Date & Time</th>
            <th style="padding:11px 14px;text-align:left;">Staff</th>
            <th style="padding:11px 14px;text-align:left;">Role</th>
            <th style="padding:11px 14px;text-align:left;">Action</th>
            <th style="padding:11px 14px;text-align:left;">Details</th>
            <th style="padding:11px 14px;text-align:right;">Amount</th>
        </tr>
    </thead>
    <tbody>
    @foreach($logs as $log)
    @php
        $actionColors = [
            'login'             => '#0a4a6e',
            'logout'            => '#64748b',
            'payment_received'  => '#1a6b3c',
            'day_tour_payment'  => '#1a6b3c',
            'booking_created'   => '#854d0e',
            'booking_confirmed' => '#1a6b3c',
            'booking_cancelled' => '#c0392b',
            'checkin'           => '#0a4a6e',
            'checkout'          => '#6b21a8',
            'room_created'      => '#854d0e',
            'room_deleted'      => '#c0392b',
            'staff_created'     => '#0a4a6e',
            'staff_deleted'     => '#c0392b',
        ];
        $actionColor = $actionColors[$log['action'] ?? ''] ?? '#555';
        $actionLabel = str_replace('_', ' ', ucfirst($log['action'] ?? '-'));
    @endphp
    <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:10px 14px;white-space:nowrap;color:#666;">
            {{ \Carbon\Carbon::parse($log['created_at'])->format('M d, Y') }}<br>
            <span style="font-size:11px;">{{ \Carbon\Carbon::parse($log['created_at'])->format('h:i A') }}</span>
        </td>
        <td style="padding:10px 14px;font-weight:600;">{{ $log['staff_name'] ?? '-' }}</td>
        <td style="padding:10px 14px;">
            <span style="font-size:11px;background:#f1f5f9;color:#475569;padding:2px 8px;border-radius:4px;text-transform:capitalize;">
                {{ str_replace('_',' ', $log['staff_role'] ?? '-') }}
            </span>
        </td>
        <td style="padding:10px 14px;">
            <span style="background:{{ $actionColor }};color:white;padding:3px 9px;border-radius:5px;font-size:11px;font-weight:500;white-space:nowrap;">
                {{ $actionLabel }}
            </span>
        </td>
        <td style="padding:10px 14px;color:#555;max-width:280px;">
            {{ $log['target_label'] ?? $log['notes'] ?? '-' }}
        </td>
        <td style="padding:10px 14px;text-align:right;font-weight:600;color:{{ ($log['amount'] ?? 0) > 0 ? '#1a6b3c' : '#aaa' }};">
            @if(($log['amount'] ?? 0) > 0)
                ₱{{ number_format($log['amount'], 2) }}
            @else
                —
            @endif
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

@endsection
