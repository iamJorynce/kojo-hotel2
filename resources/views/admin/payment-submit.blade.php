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
    <h2>💰 Submit Daily Payments</h2>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

@if($submitted && is_array($submitted))
<div style="background:#e8f5e9;border:2px solid #1a6b3c;padding:16px;border-radius:10px;margin-bottom:20px;">
    <p style="margin:0;font-weight:600;color:#1a6b3c;">✅ Already Submitted</p>
    <p style="font-size:12px;color:#666;margin:4px 0 0 0;">
        Submitted at {{ date('M d, Y h:i A', strtotime($submitted['created_at'] ?? now())) }}
        — Status: <strong>{{ strtoupper($submitted['status'] ?? 'PENDING') }}</strong>
    </p>
    @if(isset($submitted['status']) && $submitted['status'] === 'approved')
    <p style="font-size:12px;color:#1a6b3c;margin:4px 0 0 0;">✓ Approved by {{ $submitted['admin_name'] ?? 'Admin' }}</p>
    @endif
</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    {{-- PAYMENTS LIST --}}
    <div class="card">
        <h3>Payments for {{ date('M d, Y', strtotime($date)) }}</h3>

        @if($todayPayments->isEmpty())
            <div style="text-align:center;padding:40px;color:#999;">
                No payments recorded yet today.
            </div>
        @else
            <div style="max-height:500px;overflow-y:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f0f0f0;position:sticky;top:0;">
                        <th style="padding:10px;text-align:left;">Guest</th>
                        <th style="padding:10px;text-align:left;">Type</th>
                        <th style="padding:10px;text-align:right;">Amount</th>
                        <th style="padding:10px;text-align:left;">Time</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($todayPayments as $p)
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:10px;">{{ $p['guest_name'] ?? '-' }}</td>
                    <td style="padding:10px;">
                        <span style="background:{{ $p['target_type'] === 'booking' ? '#0a4a6e' : ($p['target_type'] === 'day_tour' ? '#854d0e' : '#1a6b3c') }};color:white;padding:2px 6px;border-radius:4px;font-size:11px;">
                            {{ $p['target_type'] === 'booking' ? '🏠' : ($p['target_type'] === 'day_tour' ? '🏖' : '⛱') }}
                        </span>
                    </td>
                    <td style="padding:10px;text-align:right;font-weight:600;">₱{{ number_format($p['amount_received'], 2) }}</td>
                    <td style="padding:10px;font-size:11px;color:#666;">{{ date('h:i A', strtotime($p['received_at'])) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    {{-- SUMMARY & SUBMIT --}}
    <div class="card">
        <h3>Summary</h3>

        <div style="background:#f0f0f0;padding:16px;border-radius:8px;margin-bottom:16px;text-align:center;">
            <p style="font-size:12px;color:#666;margin:0;">Total Cash</p>
            <p style="font-size:32px;font-weight:700;color:#1a6b3c;margin:4px 0 0 0;">₱{{ number_format($totalCash, 2) }}</p>
        </div>

        <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:16px;">
            <p style="margin:0;font-size:12px;"><strong>Transactions:</strong> {{ $paymentCount }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Date:</strong> {{ date('M d, Y', strtotime($date)) }}</p>
        </div>

        @if(!$submitted)
        <form method="POST" style="display:grid;gap:12px;">
            @csrf
            <div>
                <label>Notes (optional)</label>
                <textarea name="notes" rows="3" style="width:100%;padding:10px;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;">
                Submit for Approval
            </button>
        </form>
        @else
        <div style="text-align:center;padding:16px;color:#666;">
            <p style="margin:0;">Awaiting admin approval...</p>
        </div>
        @endif
    </div>

</div>

@endsection
