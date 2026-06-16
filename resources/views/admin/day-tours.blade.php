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
    <h2>🏖 Day Tour Bookings</h2>
    <a href="/admin/day-tours/walkin" class="btn btn-success">➕ Walk-in</a>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

{{-- TODAY STATS --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px;">
    <div class="card" style="text-align:center;border-top:4px solid #0a4a6e;">
        <p style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px;">Today's Guests</p>
        <p style="font-size:32px;font-weight:700;color:#0a4a6e;">{{ $todayGuests }}</p>
    </div>
    <div class="card" style="text-align:center;border-top:4px solid #1a6b3c;">
        <p style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px;">Today's Revenue</p>
        <p style="font-size:32px;font-weight:700;color:#1a6b3c;">₱{{ number_format($todayRevenue, 2) }}</p>
    </div>
</div>

{{-- FILTERS --}}
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;align-items:center;">

    {{-- STATUS TABS --}}
    @foreach(['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','cancelled'=>'Cancelled'] as $key => $label)
    <a href="/admin/day-tours?status={{ $key }}{{ request('date') ? '&date='.request('date') : '' }}"
       style="padding:7px 14px;border-radius:6px;font-size:13px;text-decoration:none;
       background:{{ $status === $key ? '#0a4a6e' : '#eee' }};
       color:{{ $status === $key ? '#fff' : '#333' }};">
        {{ $label }}
    </a>
    @endforeach

    {{-- DATE FILTER --}}
    <form method="GET" action="/admin/day-tours" style="display:flex;gap:8px;margin-left:auto;">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="date" name="date" value="{{ $date }}"
               style="padding:7px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
        <button type="submit" class="btn btn-primary" style="padding:7px 14px;font-size:13px;">Filter</button>
        @if($date)
        <a href="/admin/day-tours?status={{ $status }}" class="btn" style="padding:7px 14px;font-size:13px;">Clear</a>
        @endif
    </form>

</div>

{{-- TABLE --}}
@if($dayTours->isEmpty())
    <div class="card" style="text-align:center;padding:40px;color:#999;">
        No day tour bookings found.
    </div>
@else
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
    <thead>
        <tr style="background:#0a4a6e;color:white;font-size:13px;">
            <th style="padding:12px 14px;text-align:left;">Guest</th>
            <th style="padding:12px 14px;text-align:left;">Package</th>
            <th style="padding:12px 14px;text-align:center;">Guests</th>
            <th style="padding:12px 14px;text-align:left;">Visit Date</th>
            <th style="padding:12px 14px;text-align:left;">Type</th>
            <th style="padding:12px 14px;text-align:right;">Total</th>
            <th style="padding:12px 14px;text-align:right;">Balance</th>
            <th style="padding:12px 14px;text-align:center;">Payment</th>
            <th style="padding:12px 14px;text-align:center;">Status</th>
            <th style="padding:12px 14px;text-align:center;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($dayTours as $t)
    @php
        $st  = $t['status']         ?? 'pending';
        $pay = $t['payment_status'] ?? 'unpaid';
        $stColor  = ['pending'=>'orange','confirmed'=>'#1a6b3c','cancelled'=>'#c0392b'][$st]  ?? '#555';
        $payColor = ['paid'=>'#1a6b3c','partial'=>'#d97706','unpaid'=>'#c0392b'][$pay] ?? '#555';
    @endphp
    <tr style="border-bottom:1px solid #f0f0f0;font-size:13px;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:11px 14px;">
            <b>{{ $t['full_name'] }}</b><br>
            <span style="color:#888;font-size:12px;">{{ $t['phone'] ?? '-' }}</span>
        </td>
        <td style="padding:11px 14px;">{{ $t['package_name'] ?? '-' }}</td>
        <td style="padding:11px 14px;text-align:center;font-weight:600;">{{ $t['guest_count'] }}</td>
        <td style="padding:11px 14px;">{{ $t['visit_date'] }}</td>
        <td style="padding:11px 14px;">
            <span style="background:{{ $t['type'] === 'walk_in' ? '#e0f2fe' : '#fef9c3' }};color:{{ $t['type'] === 'walk_in' ? '#0369a1' : '#854d0e' }};padding:3px 8px;border-radius:4px;font-size:11px;font-weight:500;">
                {{ $t['type'] === 'walk_in' ? 'Walk-in' : 'Advance' }}
            </span>
        </td>
        <td style="padding:11px 14px;text-align:right;font-weight:600;">₱{{ number_format($t['total_amount'] ?? 0, 2) }}</td>
        <td style="padding:11px 14px;text-align:right;color:{{ ($t['balance_amount'] ?? 0) > 0 ? '#c0392b' : '#1a6b3c' }};font-weight:600;">
            ₱{{ number_format($t['balance_amount'] ?? 0, 2) }}
        </td>
        <td style="padding:11px 14px;text-align:center;">
            <span style="background:{{ $payColor }};color:white;padding:3px 9px;border-radius:5px;font-size:11px;font-weight:500;text-transform:capitalize;">
                {{ str_replace('_',' ',$pay) }}
            </span>
        </td>
        <td style="padding:11px 14px;text-align:center;">
            <span style="background:{{ $stColor }};color:white;padding:3px 9px;border-radius:5px;font-size:11px;font-weight:500;text-transform:capitalize;">
                {{ $st }}
            </span>
        </td>
        <td style="padding:11px 14px;text-align:center;">
            <div style="display:flex;gap:5px;justify-content:center;flex-wrap:wrap;">

                @if($st === 'pending')
                    <a href="/admin/day-tours/confirm/{{ $t['id'] }}"
                       class="btn btn-success"
                       style="font-size:11px;padding:5px 9px;"
                       onclick="return confirm('Confirm this tour?')">✓ Confirm</a>
                @endif

                @if($pay !== 'paid' && $st !== 'cancelled')
                    <a href="/admin/day-tours/payment/{{ $t['id'] }}"
                       class="btn btn-primary"
                       style="font-size:11px;padding:5px 9px;">💳 Pay</a>
                @endif

                @if($st !== 'cancelled' && $pay !== 'paid')
                    <a href="/admin/day-tours/cancel/{{ $t['id'] }}"
                       class="btn btn-danger"
                       style="font-size:11px;padding:5px 9px;"
                       onclick="return confirm('Cancel this tour?')">✕</a>
                @endif

            </div>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

@endsection
