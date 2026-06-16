<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - Sea Eagle Beach Resort</title>
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; color: #111; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 30px; max-width: 600px; margin: 40px auto; }
        .form-group { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        .btn-primary { width: 100%; padding: 14px; background: #0a4a6e; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 10px; }
        .btn-primary:hover { background: #0d6efd; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .alert-danger { background: #fff0f0; color: #c62828; border: 1px solid #ffcccc; }
        .alert-success { background: #f0fff4; color: #2f855a; border: 1px solid #c6f6d5; }
    </style>
</head>
<body>

<!-- HEADER (Same as above) -->
<header style="background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
        <a href="/" style="text-decoration: none; color: #0a4a6e; font-size: 22px; font-weight: bold;">🦅 Sea Eagle Resort</a>
        <nav style="display: flex; gap: 20px;">
            <a href="/" style="text-decoration: none; color: #333; font-weight: 500;">Home</a>
            <a href="/rooms" style="text-decoration: none; color: #0a4a6e; font-weight: bold;">Rooms</a>
            <a href="/#contact" style="text-decoration: none; color: #333; font-weight: 500;">Contact us</a>
            <a href="/admin/login" style="text-decoration: none; color: #0a4a6e; font-weight: bold;">Admin</a>
        </nav>
    </div>
</header>

<!-- MAIN CONTENT -->
<main class="container">
    <div class="card">
        <h2 style="text-align: center; color: #0a4a6e; margin-bottom: 20px;">Book Room: {{ $room['name'] ?? 'Room' }}</h2>
        
        @php
            $price = $room['price'] ?? 0;
            $dp = $room['downpayment'] ?? ($price * 0.5);
            $balance = $room['balance'] ?? ($price - $dp);
            $today = date('Y-m-d');
        @endphp

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="/book/{{ $room['id'] ?? $room['uuid_id'] }}">
            @csrf
            <div class="form-group">
                <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label style="font-size: 13px; color: #666; display: block; margin-bottom: 5px;">Check-in</label>
                    <input type="date" name="check_in" class="form-control" required min="{{ $today }}" value="{{ request('check_in') }}">
                </div>
                <div class="form-group">
                    <label style="font-size: 13px; color: #666; display: block; margin-bottom: 5px;">Check-out</label>
                    <input type="date" name="check_out" class="form-control" required min="{{ $today }}" value="{{ request('check_out') }}">
                </div>
            </div>

            <div style="background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #d0e8ff; font-size: 14px;">
                <p style="margin: 0 0 8px;"><b>Price per night:</b> ₱{{ number_format($price, 2) }}</p>
                <p style="margin: 0 0 8px;"><b>Downpayment (50%):</b> ₱{{ number_format($dp, 2) }}</p>
                <p style="margin: 0;"><b>Balance on arrival:</b> ₱{{ number_format($balance, 2) }}</p>
            </div>

            <button type="submit" class="btn-primary">Confirm Booking</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="/rooms" style="color: #0a4a6e; text-decoration: none; font-size: 14px; font-weight: 500;">← Back to Rooms</a>
        </div>
    </div>
</main>

<!-- FOOTER (Same as above) -->
<footer style="background: #0a4a6e; color: white; padding: 40px 20px; margin-top: 60px;">
    <div class="container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
        <div>
            <h3 style="margin-top: 0;">🦅 Sea Eagle Resort</h3>
            <p style="color: #cbd5e1; font-size: 14px; line-height: 1.6;">A premier beachfront destination in Davao de Oro — where the mountains meet the sea.</p>
        </div>
        <div>
            <h4 style="margin-top: 0;">Contact Us</h4>
            <p style="color: #cbd5e1; font-size: 14px; margin: 5px 0;">📍 Pindasan, Mabini, Davao de Oro</p>
            <p style="color: #cbd5e1; font-size: 14px; margin: 5px 0;">📞 <a href="tel:+639454130470" style="color: white; text-decoration: none;">0945 413 0470</a></p>
            <p style="color: #cbd5e1; font-size: 14px; margin: 5px 0;">✉️ <a href="mailto:seaeaglecorp@gmail.com" style="color: white; text-decoration: none;">seaeaglecorp@gmail.com</a></p>
        </div>
        <div>
            <h4 style="margin-top: 0;">Quick Links</h4>
            <p style="margin: 5px 0;"><a href="/" style="color: #cbd5e1; text-decoration: none;">Home</a></p>
            <p style="margin: 5px 0;"><a href="/rooms" style="color: #cbd5e1; text-decoration: none;">Our Rooms</a></p>
            <p style="margin: 5px 0;"><a href="/admin/login" style="color: #cbd5e1; text-decoration: none;">Admin Login</a></p>
        </div>
    </div>
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); color: #cbd5e1; font-size: 13px;">
        © {{ date('Y') }} Sea Eagle Beach Resort Corp. All rights reserved.
    </div>
</footer>

</body>
</html>