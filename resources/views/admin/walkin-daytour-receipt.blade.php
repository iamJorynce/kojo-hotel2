@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>🧾 Receipt - Day Tour</h2>
    <button onclick="window.print()" class="btn" style="background:#0a4a6e;color:white;">🖨️ Print</button>
    <a href="/admin/walkin/daytours" class="btn">← Back</a>
</div>

<div style="max-width:500px;margin:20px auto;background:white;padding:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);">

    {{-- HEADER --}}
    <div style="text-align:center;margin-bottom:30px;border-bottom:2px solid #333;padding-bottom:20px;">
        <h1 style="margin:0;font-size:24px;">🌴 SEA EAGLE RESORT</h1>
        <p style="margin:4px 0 0 0;color:#666;font-size:12px;">General Santos City</p>
    </div>

    {{-- RECEIPT TITLE --}}
    <div style="text-align:center;margin-bottom:20px;">
        <h2 style="margin:0;font-size:16px;">DAY TOUR RECEIPT</h2>
        <p style="margin:8px 0 0 0;color:#666;font-size:12px;">{{ date('M d, Y h:i A') }}</p>
    </div>

    {{-- TRANSACTION ID --}}
    <div style="background:#f0f0f0;padding:12px;border-radius:6px;margin-bottom:20px;text-align:center;">
        <p style="margin:0;font-size:11px;color:#666;">Transaction ID</p>
        <p style="margin:4px 0 0 0;font-weight:600;font-size:13px;">{{ $tour['transaction_id'] }}</p>
    </div>

    {{-- GUEST INFO --}}
    <div style="margin-bottom:20px;">
        <p style="margin:0;font-size:12px;"><strong>Guest Name:</strong> {{ $tour['guest_name'] }}</p>
        <p style="margin:4px 0 0 0;font-size:12px;"><strong>Phone:</strong> {{ $tour['guest_phone'] }}</p>
        @if($tour['guest_email'])
        <p style="margin:4px 0 0 0;font-size:12px;"><strong>Email:</strong> {{ $tour['guest_email'] }}</p>
        @endif
    </div>

    <hr style="border:none;border-top:1px dashed #ddd;margin:20px 0;">

    {{-- ITEMS --}}
    <div style="margin-bottom:20px;">
        <h4 style="margin:0 0 12px 0;font-size:12px;font-weight:600;">SERVICES</h4>
        
        @foreach($tour['items'] as $item)
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:11px;">
            <div>
                <strong>{{ $item['item_name'] }}</strong><br>
                @if($item['item_type'] === 'package')
                    {{ $item['guest_count'] }} guest(s) × ₱{{ number_format($item['price_per_unit'], 2) }}
                @elseif($item['item_type'] === 'cottage')
                    {{ $item['quantity'] }} night(s) × ₱{{ number_format($item['price_per_unit'], 2) }}
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
            <span>₱{{ number_format($tour['total_amount'], 2) }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:12px;border-top:1px solid #ddd;padding-top:8px;">
            <strong>TOTAL:</strong>
            <strong style="font-size:14px;">₱{{ number_format($tour['total_amount'], 2) }}</strong>
        </div>
    </div>

    {{-- PAYMENT STATUS --}}
    <div style="background:#f0f0f0;padding:12px;border-radius:6px;margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:8px;">
            <span>Paid:</span>
            <strong>₱{{ number_format($tour['paid_amount'] ?? 0, 2) }}</strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:11px;">
            <span>Balance:</span>
            <strong>₱{{ number_format($tour['balance_amount'] ?? 0, 2) }}</strong>
        </div>
    </div>

    {{-- PAYMENT INFO --}}
    <div style="background:#e8f4f8;padding:12px;border-radius:6px;margin-bottom:20px;text-align:center;font-size:11px;">
        <p style="margin:0;color:#666;">Payment Status</p>
        <p style="margin:4px 0 0 0;font-weight:600;font-size:12px;color:#0a4a6e;">
            {{ strtoupper($tour['payment_status']) }}
        </p>
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
