<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif; /* dompdf-safe font */
            font-size: 13px;
            color: #222;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #0a4a6e;
            padding-bottom: 12px;
        }

        .header h1 {
            font-size: 22px;
            color: #0a4a6e;
            letter-spacing: 1px;
        }

        .header p {
            font-size: 12px;
            color: #555;
            margin-top: 3px;
        }

        .receipt-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            font-size: 12px;
            color: #444;
        }

        .section-title {
            background: #0a4a6e;
            color: white;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table td {
            padding: 6px 4px;
            border-bottom: 1px solid #eee;
        }

        table td:first-child {
            font-weight: bold;
            width: 45%;
            color: #444;
        }

        .totals td {
            padding: 5px 4px;
        }

        .totals .grand-total td {
            font-weight: bold;
            font-size: 15px;
            color: #0a4a6e;
            border-top: 2px solid #0a4a6e;
            padding-top: 8px;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
            background: #28a745;
            color: white;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #888;
            border-top: 1px dashed #ccc;
            padding-top: 12px;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            font-size: 12px;
        }

        .sig-block {
            text-align: center;
            width: 45%;
        }

        .sig-block .sig-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>Sea Eagle Beach Resort</h1>
        <p>Brgy. Pindasan, Mabini, Davao de Oro. Philippines</p>
        <p>Phone No.: 0945 413 0470 &nbsp;|&nbsp; seaeaglecorp@gmail.com</p>
    </div>

    {{-- OR META --}}
    <div class="receipt-meta">
        <div>
            <strong>Official Receipt No:</strong> {{ $receiptNumber }}<br>
            <strong>Date Issued:</strong> {{ $issuedAt }}
        </div>
        <div style="text-align:right;">
            <strong>Status:</strong>
            <span class="badge">{{ strtoupper($b['status']) }}</span>
        </div>
    </div>

    {{-- GUEST INFO --}}
    <div class="section-title">GUEST INFORMATION</div>
    <table>
        <tr><td>Full Name</td><td>{{ $b['full_name'] }}</td></tr>
        <tr><td>Phone</td><td>{{ $b['phone'] ?? '—' }}</td></tr>
    </table>

    {{-- BOOKING DETAILS --}}
    <div class="section-title">BOOKING DETAILS</div>
    <table>
        <tr><td>Room</td><td>{{ $b['room_name'] ?? '—' }}</td></tr>
        <tr><td>Room Number</td><td>{{ $b['room_number'] ?? '—' }}</td></tr>
        <tr><td>Check-in Date</td><td>{{ $b['check_in'] }}</td></tr>
        <tr><td>Check-out Date</td><td>{{ $b['check_out'] }}</td></tr>
    </table>

    {{-- PAYMENT SUMMARY --}}
    <div class="section-title">PAYMENT SUMMARY</div>
    <table class="totals">
        <tr>
            <td>Total Amount</td>
            <td>₱{{ number_format($b['total_amount'], 2) }}</td>
        </tr>
        <tr>
            <td>Amount Paid</td>
            <td>₱{{ number_format($b['paid_amount'], 2) }}</td>
        </tr>
        <tr>
            <td>Balance</td>
            <td>₱{{ number_format($b['balance_amount'], 2) }}</td>
        </tr>
        <tr class="grand-total">
            <td>Payment Status</td>
            <td>{{ strtoupper(str_replace('_', ' ', $b['payment_status'])) }}</td>
        </tr>
    </table>

    {{-- SIGNATURES --}}
    <div class="signature-row">
        <div class="sig-block">
            <div class="sig-line">Cashier / Front Desk</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">Guest Signature</div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        This is an official receipt. Thank you for staying with us!<br>
        Generated on {{ $issuedAt }}
    </div>

</body>
</html>
