@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>🧾 Receipt - Room Booking</h2>
    <button onclick="window.print()" class="btn" style="background:#0a4a6e;color:white;">🖨️ Print</button>
    <a href="/admin/walkin/bookings" class="btn">← Back</a>
</div>

<div style="max-width:500px;margin:20px auto;background:white;padding:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">

    {{-- HEADER --}}
    <div style="text-align:center;margin-bottom:30px;border-bottom:2px solid #333;padding-bottom:20px;">
        <h1 style="margin:0;font-size:24px;">🌴 SEA EAGLE RESORT</h1>
        <p style="margin:4px 0 0 0;color:#666;font-size:12px;">General Santos City</p>
    </div>

    {{-- RECEIPT TITLE --}}
    <div style="text-align:center;margin-bottom:20px;">
        <h2 style="margin:0;font-size:16px;">ROOM BOOKING RECEIPT</h2>
        <p style="margin:8px 0 0 0;color:#666;font-size:12px;">{{ date('M d, Y h:i A') }}</p>
    </div>

    {{-- TRANSACTION ID --}}
    <div style="background:#f0f0f0;padding:12px;border-radius:6px;margin-bottom:20px;text-align:center;">
        <p style="margin:0;font-size:11px;color:#666;">Booking ID</p>
        <p style="margin:4px 0 0 0;font-weight:600;font-size:13px;">{{ $booking['transaction_id'] }}</p>
    </div>

    {{-- GUEST & DATES --}}
    <div style="margin-bottom:20px;">
        <p style="margin:0;font-size:12px;"><strong>Guest Name:</strong> {{ $booking['guest_name'] }}</p>
        <p style="margin:4px 0 0 0;font-size:12px;"><strong>Phone:</strong> {{ $booking['guest_phone'] }}</p>
        @if($booking['guest_email'])
        <p style="margin:4px 0 0 0;font-size:12px;"><strong>Email:</strong> {{ $booking['guest_email'] }}</p>
        @endif
    </div>

    {{-- BOOKING DATES --}}
    <div style="background:#e8f4f8;padding:12px;border-radius:6px;margin-bottom:20px;font-size:12px;">
        <p style="margin:0;"><strong>Check-In:</strong> {{ date('M d, Y', strtotime($booking['check_in'])) }}</p>
        <p style="margin:4px 0 0 0;"><strong>Check-Out:</strong> {{ date('M d, Y', strtotime($booking['check_out'])) }}</p>
        <p style="margin:4px 0 0 0;"><strong>Number of Nights:</strong> {{ $booking['number_of_nights'] }}</p>
    </div>

    <hr style="border:none;border-top:1px dashed #ddd;margin:20px 0;">

    {{-- ITEMS --}}
    <div style="margin-bottom:20px;">
        <h4 style="margin:0 0 12px 0;font-size:12px;font-weight:600;">ACCOMMODATIONS & SERVICES</h4>
        
        @foreach($booking['items'] as $item)
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:11px;">
            <div>
                <strong>{{ $item['item_name'] }}</strong><br>
                @if($item['item_type'] === 'room' || $item['item_type'] === 'cottage')
                    {{ $item['number_of_nights'] }} night(s) × ₱{{ number_format($item['price_per_night'], 2) }}
                @else
                    {{ $item['quantity'] }} × ₱{{ number_format($item['price_per_unit'], 2) }}
                @endif
            </div>
            <div style="text-align:right;font-weight:600;">
                ₱{{ number_format($item['subtotal'], 2) }}
            </div>
        </div>
        @endforeach
    </div>

    <hr style="border:none;border-top:1px dashed #ddd;margin:20px 0;">

    {{-- TOTALS --}}
    <div style="margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:12px;">
            <span>Subtotal:</span>
            <span>₱{{ number_format($booking['total_amount'], 2) }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:12px;border-top:1px solid #ddd;padding-top:8px;">
            <strong>TOTAL:</strong>
            <strong style="font-size:14px;">₱{{ number_format($booking['total_amount'], 2) }}</strong>
        </div>
    </div>

    {{-- PAYMENT STATUS --}}
    <div style="background:#f0f0f0;padding:12px;border-radius:6px;margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:8px;">
            <span>Paid:</span>
            <strong>₱{{ number_format($booking['paid_amount'] ?? 0, 2) }}</strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:11px;">
            <span>Balance:</span>
            <strong>₱{{ number_format($booking['balance_amount'] ?? 0, 2) }}</strong>
        </div>
    </div>

    {{-- PAYMENT & BOOKING INFO --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;font-size:10px;">
        <div style="background:#e8f4f8;padding:10px;border-radius:6px;text-align:center;">
            <p style="margin:0;color:#666;">Payment Status</p>
            <p style="margin:4px 0 0 0;font-weight:600;color:#0a4a6e;">{{ strtoupper($booking['payment_status']) }}</p>
        </div>
        <div style="background:#fffbf0;padding:10px;border-radius:6px;text-align:center;">
            <p style="margin:0;color:#666;">Booking Status</p>
            <p style="margin:4px 0 0 0;font-weight:600;color:#f39c12;">{{ strtoupper($booking['booking_status']) }}</p>
        </div>
    </div>

    {{-- FOOTER --}}
    <div style="text-align:center;margin-top:30px;padding-top:20px;border-top:1px solid #ddd;font-size:10px;color:#999;">
        <p style="margin:0;">Thank you for choosing Sea Eagle Resort!</p>
        <p style="margin:4px 0 0 0;">{{ date('M d, Y H:i') }}</p>
    </div>

</div>

<style>
    @media print {
        body { background: white; }
        .topbar { display: none; }
        div { box-shadow: none !important; }
    }
</style>

@endsection
