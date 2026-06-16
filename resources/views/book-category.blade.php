<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Category - Sea Eagle Beach Resort</title>
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; color: #111; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; max-width: 900px; margin: 40px auto; }
        .form-group { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        .btn-primary { width: 100%; padding: 14px; background: #0a4a6e; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 10px; }
        .btn-primary:hover { background: #0d6efd; }
        .error-box { display: none; margin-top: 10px; padding: 10px 14px; background: #fff0f0; border: 1px solid #ffcccc; border-radius: 8px; color: #c62828; font-size: 13px; }
    </style>
</head>
<body>

<!-- HEADER -->
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
    @if(!$room)
        <div class="card" style="padding: 40px; text-align: center; color: #c62828;">
            <h2>No available room for this category.</h2>
            <a href="/rooms" style="color: #0a4a6e; text-decoration: none; font-weight: bold;">← Back to Rooms</a>
        </div>
    @else
        <div class="card">
            <div style="position: relative;">
                <img src="{{ $room['image_url'] }}" style="width: 100%; height: 320px; object-fit: cover;">
                <div style="position: absolute; bottom: 15px; right: 15px; background: rgba(10,74,110,0.95); color: white; padding: 8px 14px; border-radius: 8px; font-weight: bold; font-size: 16px;">
                    ₱{{ number_format($room['price'], 2) }} per night
                </div>
            </div>

            <div style="padding: 28px;">
                <h2 style="margin: 0; font-size: 24px; color: #0a4a6e;">{{ $room['name'] }}</h2>
                <p style="color: #666; margin-top: 6px; line-height: 1.5;">{{ $room['description'] ?? '' }}</p>
                <div style="height: 1px; background: #eee; margin: 22px 0;"></div>

                <form method="POST" action="/book/{{ $room['uuid_id'] }}" id="bookingForm">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
                        <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                        <input type="email" name="email" class="form-control" placeholder="Email" required style="grid-column: span 2;">
                    </div>

                    @php $today = date('Y-m-d'); @endphp
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="font-size: 13px; color: #666; display: block; margin-bottom: 5px;">Check-in (Arrival Date)</label>
                            <input type="date" name="check_in" id="check_in" class="form-control" required min="{{ $today }}" value="{{ request('check_in') }}">
                        </div>
                        <div>
                            <label style="font-size: 13px; color: #666; display: block; margin-bottom: 5px;">Check-out (Departure Date)</label>
                            <input type="date" name="check_out" id="check_out" class="form-control" required min="{{ $today }}" value="{{ request('check_out') }}">
                        </div>
                    </div>

                    <div id="date-error" class="error-box">⚠️ Check-out date must be after check-in date.</div>

                    <div style="margin-top: 18px; padding: 14px; background: #f0f8ff; border-radius: 8px; border: 1px solid #d0e8ff;">
                        <p style="margin: 0 0 6px; font-size: 14px;"><b>No. of Nights:</b> <span id="nights">0</span></p>
                        <p style="margin: 0; font-size: 15px;"><b>Total:</b> ₱<span id="total">0.00</span></p>
                    </div>

                    <button type="submit" class="btn-primary">Confirm Booking</button>
                </form>
            </div>
        </div>
    @endif
</main>

<!-- FOOTER -->
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const checkIn = document.getElementById("check_in");
    const checkOut = document.getElementById("check_out");
    const nightsEl = document.getElementById("nights");
    const totalEl = document.getElementById("total");
    const errorEl = document.getElementById("date-error");
    const form = document.getElementById("bookingForm");
    const pricePerNight = {{ $room['price'] ?? 0 }};

    function formatCurrency(amount) {
        return amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function calculate() {
        if (checkIn.value && checkOut.value) {
            const inDate = new Date(checkIn.value);
            const outDate = new Date(checkOut.value);
            const diff = (outDate - inDate) / (1000 * 60 * 60 * 24);

            if (diff > 0) {
                nightsEl.innerText = diff;
                totalEl.innerText = formatCurrency(pricePerNight * diff);
                errorEl.style.display = 'none';
            } else {
                nightsEl.innerText = 0;
                totalEl.innerText = '0.00';
                errorEl.style.display = 'block';
            }
        }
    }

    checkIn.addEventListener("change", function () {
        checkOut.min = checkIn.value;
        if (checkOut.value && checkOut.value <= checkIn.value) {
            checkOut.value = '';
            nightsEl.innerText = 0;
            totalEl.innerText = '0.00';
        }
        calculate(); 
    });

    checkOut.addEventListener("change", calculate);

    form.addEventListener("submit", function (e) {
        if (!checkIn.value || !checkOut.value || checkOut.value <= checkIn.value) {
            e.preventDefault();
            errorEl.style.display = 'block';
            checkOut.focus();
        }
    });

    if (checkIn.value) {
        checkOut.min = checkIn.value;
    }
    calculate();
});
</script>

</body>
</html>