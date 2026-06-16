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
    <h2>📋 Payment Submission Details</h2>
    <a href="/admin/payment-submissions" class="btn">← Back</a>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    {{-- PAYMENTS --}}
    <div class="card">
        <h3>Payment Items</h3>
        
        @if($items->isEmpty())
            <div style="text-align:center;padding:20px;color:#999;">No items</div>
        @else
        <div style="max-height:600px;overflow-y:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f0f0f0;position:sticky;top:0;">
                    <th style="padding:10px;text-align:left;">Guest</th>
                    <th style="padding:10px;text-align:left;">Type</th>
                    <th style="padding:10px;text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:10px;">{{ $item['guest_name'] ?? '-' }}</td>
                <td style="padding:10px;">
                    <span style="background:{{ $item['target_type'] === 'booking' ? '#0a4a6e' : ($item['target_type'] === 'day_tour' ? '#854d0e' : '#1a6b3c') }};color:white;padding:2px 6px;border-radius:4px;font-size:11px;">
                        {{ ucfirst($item['target_type']) }}
                    </span>
                </td>
                <td style="padding:10px;text-align:right;font-weight:600;">₱{{ number_format($item['amount'], 2) }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

    {{-- SUMMARY & ACTIONS --}}
    <div class="card">
        <h3>Summary</h3>

        <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:16px;">
            <p style="margin:0;font-size:12px;"><strong>Staff:</strong> {{ $submission['staff_name'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Date:</strong> {{ date('M d, Y', strtotime($submission['submission_date'])) }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Transactions:</strong> {{ $submission['payment_count'] }}</p>
        </div>

        <div style="background:#e8f4f8;padding:14px;border-radius:8px;margin-bottom:16px;text-align:center;border-left:4px solid #0a4a6e;">
            <p style="font-size:12px;color:#666;margin:0;">Total Cash</p>
            <p style="font-size:28px;font-weight:700;color:#0a4a6e;margin:4px 0 0 0;">₱{{ number_format($submission['total_cash'], 2) }}</p>
        </div>

        <div style="background:{{ $submission['status'] === 'approved' ? '#e8f5e9' : ($submission['status'] === 'rejected' ? '#fadbd8' : '#fff3cd') }};padding:12px;border-radius:8px;margin-bottom:16px;border-left:4px solid {{ $submission['status'] === 'approved' ? '#1a6b3c' : ($submission['status'] === 'rejected' ? '#c0392b' : '#f39c12') }};">
            <p style="margin:0;font-weight:600;font-size:12px;">
                Status: <span style="text-transform:uppercase;">{{ $submission['status'] }}</span>
            </p>
            @if($submission['status'] === 'approved')
                <p style="margin:4px 0 0 0;font-size:11px;color:#1a6b3c;">
                    ✓ Approved by {{ $submission['admin_name'] }} on {{ date('M d, Y h:i A', strtotime($submission['approved_at'])) }}
                </p>
            @elseif($submission['status'] === 'rejected')
                <p style="margin:4px 0 0 0;font-size:11px;color:#c0392b;">
                    ✗ Rejected
                </p>
            @endif
        </div>

        @if($submission['status'] === 'pending')
        <div style="display:grid;gap:10px;">
            <button onclick="approveModal()" class="btn btn-primary" style="width:100%;padding:12px;">✓ Approve</button>
            <button onclick="rejectModal()" class="btn" style="width:100%;padding:12px;background:#c0392b;color:white;">✗ Reject</button>
        </div>
        @endif
    </div>

</div>

{{-- APPROVE MODAL --}}
<div id="approveModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;padding:30px;border-radius:12px;width:90%;max-width:400px;">
        <h3>Approve Payment?</h3>
        <p style="color:#666;">This will mark the payment as cleared and the cashier can close shift.</p>
        <form method="POST" action="/admin/payment-submission/{{ $submission['id'] }}/approve" style="display:grid;gap:12px;">
            @csrf
            <div>
                <label>Notes</label>
                <textarea name="notes" rows="2" style="width:100%;padding:10px;"></textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Approve</button>
                <button type="button" onclick="approveModal()" class="btn" style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- REJECT MODAL --}}
<div id="rejectModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;padding:30px;border-radius:12px;width:90%;max-width:400px;">
        <h3>Reject Payment?</h3>
        <p style="color:#666;">Please provide reason for rejection.</p>
        <form method="POST" action="/admin/payment-submission/{{ $submission['id'] }}/reject" style="display:grid;gap:12px;">
            @csrf
            <div>
                <label>Reason</label>
                <textarea name="notes" rows="3" required placeholder="Why is this being rejected?" style="width:100%;padding:10px;"></textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn" style="flex:1;background:#c0392b;color:white;">Reject</button>
                <button type="button" onclick="rejectModal()" class="btn" style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function approveModal() {
    const modal = document.getElementById('approveModal');
    modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
}

function rejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
}
</script>

@endsection
