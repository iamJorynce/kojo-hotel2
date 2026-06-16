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
    <h2>📊 Daily Remittance Report</h2>
</div>

{{-- DATE PICKER --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;">
        <div style="flex:1;min-width:150px;">
            <label>Date</label>
            <input type="date" name="date" value="{{ $date }}" style="width:100%;padding:10px;">
        </div>
        <button type="submit" class="btn btn-primary">View Report</button>
    </form>
</div>

{{-- SUMMARY CARDS --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:24px;">

    <div class="card" style="text-align:center;border-top:4px solid #0a4a6e;">
        <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Total Submitted</p>
        <p style="font-size:28px;font-weight:700;color:#0a4a6e;">₱{{ number_format($totalSubmitted, 2) }}</p>
    </div>

    <div class="card" style="text-align:center;border-top:4px solid #1a6b3c;">
        <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Total Approved</p>
        <p style="font-size:28px;font-weight:700;color:#1a6b3c;">₱{{ number_format($totalApproved, 2) }}</p>
        <p style="font-size:12px;color:#666;margin-top:4px;">{{ $approvedCount }} submission(s)</p>
    </div>

    <div class="card" style="text-align:center;border-top:4px solid #f39c12;">
        <p style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Pending Approval</p>
        <p style="font-size:28px;font-weight:700;color:#f39c12;">₱{{ number_format($totalPending, 2) }}</p>
    </div>

</div>

{{-- SUBMISSIONS TABLE --}}
<div class="card">
    <h3>Submissions for {{ date('M d, Y', strtotime($date)) }}</h3>

    @if($submissions->isEmpty())
        <div style="text-align:center;padding:40px;color:#999;">
            No submissions for this date.
        </div>
    @else
    <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);font-size:13px;">
        <thead>
            <tr style="background:#0f172a;color:white;">
                <th style="padding:11px 14px;text-align:left;">Submitted By</th>
                <th style="padding:11px 14px;text-align:right;">Total Cash</th>
                <th style="padding:11px 14px;text-align:center;">Transactions</th>
                <th style="padding:11px 14px;text-align:center;">Status</th>
                <th style="padding:11px 14px;text-align:left;">Approved By</th>
                <th style="padding:11px 14px;text-align:center;">Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($submissions as $sub)
        <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
            <td style="padding:10px 14px;font-weight:600;">
                {{ $sub['staff_name'] ?? '-' }}<br>
                <span style="font-size:11px;color:#666;">{{ date('h:i A', strtotime($sub['created_at'])) }}</span>
            </td>
            <td style="padding:10px 14px;text-align:right;font-weight:700;color:#1a6b3c;font-size:15px;">
                ₱{{ number_format($sub['total_cash'], 2) }}
            </td>
            <td style="padding:10px 14px;text-align:center;">{{ $sub['payment_count'] ?? 0 }}</td>
            <td style="padding:10px 14px;text-align:center;">
                <span style="background:{{ $sub['status'] === 'approved' ? '#1a6b3c' : ($sub['status'] === 'rejected' ? '#c0392b' : '#f39c12') }};color:white;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;">
                    {{ strtoupper($sub['status']) }}
                </span>
            </td>
            <td style="padding:10px 14px;font-size:12px;">
                @if($sub['status'] === 'approved')
                    {{ $sub['admin_name'] ?? '-' }}<br>
                    <span style="font-size:10px;color:#666;">{{ date('h:i A', strtotime($sub['approved_at'])) }}</span>
                @else
                    —
                @endif
            </td>
            <td style="padding:10px 14px;text-align:center;">
                <a href="/admin/payment-submission/{{ $sub['id'] }}" class="btn" style="padding:4px 8px;font-size:11px;">View</a>
            </td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;font-weight:700;font-size:14px;">
                <td colspan="1" style="padding:12px 14px;text-align:right;">TOTAL:</td>
                <td style="padding:12px 14px;text-align:right;color:#1a6b3c;font-size:16px;">₱{{ number_format($totalApproved, 2) }}</td>
                <td colspan="4" style="padding:12px 14px;"></td>
            </tr>
        </tfoot>
    </table>
    </div>
    @endif
</div>

@endsection
