@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>💰 Payment - Day Tour</h2>
    <a href="/admin/walkin/daytours" class="btn">← Back</a>
</div>

@if(session('success'))
    <div class="alert-success">✅ {{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">
        @foreach($errors->all() as $error)
            ❌ {{ $error }}<br>
        @endforeach
    </div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    {{-- TRANSACTION DETAILS --}}
    <div class="card">
        <h3>Transaction Details</h3>

        <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:16px;">
            <p style="margin:0;font-size:12px;"><strong>Transaction ID:</strong> {{ $tour['transaction_id'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Guest:</strong> {{ $tour['guest_name'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Phone:</strong> {{ $tour['guest_phone'] }}</p>
        </div>

        <h4 style="margin-top:20px;margin-bottom:12px;font-size:13px;">Items</h4>

        @foreach($tour['items'] as $item)
        <div style="background:#f0f0f0;padding:10px;margin-bottom:8px;border-radius:6px;border-left:4px solid:
            @if($item['item_type'] === 'package') #0a4a6e
            @elseif($item['item_type'] === 'cottage') #f39c12
            @else #666
            @endif
        ;">
            <p style="margin:0;font-weight:600;font-size:12px;">
                @if($item['item_type'] === 'package') 📦
                @elseif($item['item_type'] === 'cottage') 🏡
                @else 🧰
                @endif
                {{ $item['item_name'] }}
            </p>
            <p style="margin:4px 0 0 0;font-size:11px;color:#666;">
                @if($item['item_type'] === 'package')
                    {{ $item['guest_count'] }} guest(s) × ₱{{ number_format($item['price_per_unit'], 2) }}
                @elseif($item['item_type'] === 'cottage')
                    {{ $item['quantity'] }} night(s) × ₱{{ number_format($item['price_per_unit'], 2) }}
                @else
                    {{ $item['quantity'] }} × ₱{{ number_format($item['price_per_unit'], 2) }}
                @endif
            </p>
            <p style="margin:4px 0 0 0;font-weight:600;color:
                @if($item['item_type'] === 'package') #0a4a6e
                @elseif($item['item_type'] === 'cottage') #f39c12
                @else #666
                @endif
            ;">
                ₱{{ number_format($item['subtotal'], 2) }}
            </p>
        </div>
        @endforeach

        <hr style="margin:20px 0;border:none;border-top:1px solid #ddd;">

        <h4 style="font-size:13px;margin:0 0 12px 0;">Payment History</h4>

        @if(empty($payments))
            <p style="color:#999;font-size:12px;">No payments yet</p>
        @else
            @foreach($payments as $payment)
            <div style="background:#e8f4f8;padding:10px;border-radius:6px;margin-bottom:8px;font-size:12px;">
                <p style="margin:0;"><strong>₱{{ number_format($payment['amount_received'], 2) }}</strong> {{ $payment['payment_method'] }}</p>
                <p style="margin:4px 0 0 0;color:#666;">{{ date('M d, h:i A', strtotime($payment['received_at'])) }}</p>
            </div>
            @endforeach
        @endif
    </div>

    {{-- PAYMENT FORM --}}
    <div class="card">
        <h3>Payment</h3>

        <div style="background:#fff3cd;padding:12px;border-radius:8px;margin-bottom:16px;border-left:4px solid #f39c12;">
            <p style="margin:0;font-size:12px;color:#666;">TOTAL AMOUNT</p>
            <p style="margin:6px 0 0 0;font-size:24px;font-weight:700;color:#f39c12;">
                ₱{{ number_format($tour['total_amount'], 2) }}
            </p>
        </div>

        <div style="background:#e8f4f8;padding:12px;border-radius:8px;margin-bottom:16px;">
            <div style="margin-bottom:8px;">
                <p style="margin:0;font-size:12px;color:#666;">Already Paid:</p>
                <p style="margin:4px 0 0 0;font-weight:600;">₱{{ number_format($tour['paid_amount'] ?? 0, 2) }}</p>
            </div>
            <div>
                <p style="margin:0;font-size:12px;color:#666;">Balance Due:</p>
                <p style="margin:4px 0 0 0;font-size:18px;font-weight:700;color:
                    @if(($tour['balance_amount'] ?? 0) <= 0) #1a6b3c
                    @else #c0392b
                    @endif
                ">
                    ₱{{ number_format($tour['balance_amount'] ?? 0, 2) }}
                </p>
            </div>
        </div>

        @if(($tour['balance_amount'] ?? 0) > 0)
        <form method="POST" style="display:grid;gap:12px;">
            @csrf

            <div>
                <label style="display:block;margin-bottom:8px;font-weight:600;">Cash Received (₱)</label>
                <input type="number" name="cash_received" step="0.01" placeholder="0.00" required
                       style="width:100%;padding:12px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-weight:600;">
                Record Payment
            </button>
        </form>
        @else
        <div style="background:#e8f8f3;padding:14px;border-radius:8px;text-align:center;border:2px solid #1a6b3c;">
            <p style="margin:0;font-size:14px;font-weight:600;color:#1a6b3c;">✅ FULLY PAID</p>
        </div>
        @endif

        <div style="margin-top:20px;padding-top:20px;border-top:1px solid #ddd;">
            <a href="/admin/walkin/daytour/{{ $tour['id'] }}/receipt" target="_blank" class="btn" 
               style="width:100%;padding:10px;background:#0a4a6e;color:white;text-align:center;text-decoration:none;">
                🖨️ Print Receipt
            </a>
        </div>
    </div>

</div>

@endsection
