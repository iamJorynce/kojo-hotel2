@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>💰 Payment - Room Booking</h2>
    <a href="/admin/walkin/bookings" class="btn">← Back</a>
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

    {{-- BOOKING DETAILS --}}
    <div class="card">
        <h3>Booking Details</h3>

        <div style="background:#f8fafc;padding:12px;border-radius:8px;margin-bottom:16px;">
            <p style="margin:0;font-size:12px;"><strong>Transaction ID:</strong> {{ $booking['transaction_id'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Guest:</strong> {{ $booking['guest_name'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Phone:</strong> {{ $booking['guest_phone'] }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Check-In:</strong> {{ date('M d, Y', strtotime($booking['check_in'])) }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Check-Out:</strong> {{ date('M d, Y', strtotime($booking['check_out'])) }}</p>
            <p style="margin:4px 0 0 0;font-size:12px;"><strong>Nights:</strong> {{ $booking['number_of_nights'] }}</p>
        </div>

        <h4 style="margin-top:20px;margin-bottom:12px;font-size:13px;">Booking Items</h4>

        @foreach($booking['items'] as $item)
        <div style="background:#f0f0f0;padding:10px;margin-bottom:8px;border-radius:6px;border-left:4px solid:
            @if($item['item_type'] === 'room') #0a4a6e
            @elseif($item['item_type'] === 'cottage') #f39c12
            @else #666
            @endif
        ;">
            <p style="margin:0;font-weight:600;font-size:12px;">
                @if($item['item_type'] === 'room') 🛏️
                @elseif($item['item_type'] === 'cottage') 🏡
                @else 🧰
                @endif
                {{ $item['item_name'] }}
            </p>
            <p style="margin:4px 0 0 0;font-size:11px;color:#666;">
                @if($item['item_type'] === 'room' || $item['item_type'] === 'cottage')
                    {{ $item['number_of_nights'] }} night(s) × ₱{{ number_format($item['price_per_night'], 2) }}
                @else
                    {{ $item['quantity'] }} × ₱{{ number_format($item['price_per_unit'], 2) }}
                @endif
            </p>
            <p style="margin:4px 0 0 0;font-weight:600;color:
                @if($item['item_type'] === 'room') #0a4a6e
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
                ₱{{ number_format($booking['total_amount'], 2) }}
            </p>
        </div>

        <div style="background:#e8f4f8;padding:12px;border-radius:8px;margin-bottom:16px;">
            <div style="margin-bottom:8px;">
                <p style="margin:0;font-size:12px;color:#666;">Already Paid:</p>
                <p style="margin:4px 0 0 0;font-weight:600;">₱{{ number_format($booking['paid_amount'] ?? 0, 2) }}</p>
            </div>
            <div>
                <p style="margin:0;font-size:12px;color:#666;">Balance Due:</p>
                <p style="margin:4px 0 0 0;font-size:18px;font-weight:700;color:
                    @if(($booking['balance_amount'] ?? 0) <= 0) #1a6b3c
                    @else #c0392b
                    @endif
                ">
                    ₱{{ number_format($booking['balance_amount'] ?? 0, 2) }}
                </p>
            </div>
        </div>

        @if(($booking['balance_amount'] ?? 0) > 0)
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

        {{-- ACTIONS --}}
        <div style="margin-top:20px;padding-top:20px;border-top:1px solid #ddd;">
            @if($booking['booking_status'] === 'confirmed')
            <form method="POST" action="/admin/walkin/booking/{{ $booking['id'] }}/checkin" style="margin-bottom:8px;">
                @csrf
                <button type="submit" class="btn" style="width:100%;padding:10px;background:#0a4a6e;color:white;">
                    🏠 Check-In
                </button>
            </form>
            @endif

            @if($booking['booking_status'] === 'checked_in')
            <form method="POST" action="/admin/walkin/booking/{{ $booking['id'] }}/checkout" style="margin-bottom:8px;">
                @csrf
                <button type="submit" class="btn" style="width:100%;padding:10px;background:#1a6b3c;color:white;">
                    👋 Check-Out
                </button>
            </form>
            @endif

            <a href="/admin/walkin/booking/{{ $booking['id'] }}/receipt" target="_blank" class="btn" 
               style="width:100%;padding:10px;background:#666;color:white;text-align:center;text-decoration:none;display:block;">
                🖨️ Print Receipt
            </a>
        </div>
    </div>

</div>

@endsection
