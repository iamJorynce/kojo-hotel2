<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Day Tour — Sea Eagle Beach Resort</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --blue: #0a4a6e; --green: #1a6b3c; --green2: #2ea05a;
            --red: #c0392b; --sand: #f5efe6; --dark: #0d1b2a;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--sand); color: #2c3e50; }

        nav {
            background: var(--dark);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav .logo { font-family: 'Cormorant Garamond', serif; color: white; font-size: 20px; text-decoration: none; }
        nav a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; margin-left: 20px; }

        .container {
            max-width: 700px;
            margin: 50px auto;
            padding: 0 20px 60px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--blue);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .back-link:hover { text-decoration: underline; }

        .package-summary {
            background: linear-gradient(135deg, var(--blue), var(--green));
            color: white;
            padding: 24px 28px;
            border-radius: 14px 14px 0 0;
        }

        .package-summary h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            margin-bottom: 4px;
        }

        .package-summary .price {
            font-size: 22px;
            color: #f0d49a;
            font-weight: 600;
        }

        .form-card {
            background: white;
            border-radius: 0 0 14px 14px;
            padding: 28px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 6px;
            margin-top: 16px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus, select:focus { border-color: var(--blue); }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        /* CALCULATOR */
        .calculator {
            background: #f0f9ff;
            border: 1.5px solid #c8e8f5;
            border-radius: 12px;
            padding: 18px 20px;
            margin: 20px 0;
        }

        .calculator h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--blue);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 5px 0;
            border-bottom: 1px solid #dce8f0;
        }

        .calc-row:last-child { border: none; font-weight: 600; font-size: 16px; color: var(--blue); }

        .error-box {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--blue), var(--green));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: opacity 0.2s;
        }

        .submit-btn:hover { opacity: 0.9; }
    </style>
</head>
<body>

<nav>
    <a href="/" class="logo">🦅 Sea Eagle Resort</a>
    <div>
        <a href="/">Home</a>
        <a href="/day-tour">Day Tour</a>
    </div>
</nav>

<div class="container">

    <a href="/day-tour" class="back-link">← Back to packages</a>

    @if(session('error'))
        <div class="error-box">{{ session('error') }}</div>
    @endif

    <div class="package-summary">
        <h2>{{ $package['name'] }}</h2>
        <div class="price">₱{{ number_format($package['price_per_person'], 2) }} per person</div>
        <p style="margin-top:6px;font-size:13px;color:rgba(255,255,255,0.75);">
            {{ $package['description'] ?? '' }}
        </p>
    </div>

    <div class="form-card">

        <form method="POST" action="/day-tour/book/{{ $package['id'] }}" id="bookingForm">
            @csrf

            <div class="grid-2">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Your full name" required>
                </div>
                <div>
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 09XX XXX XXXX" required>
                </div>
            </div>

            <label>Email (optional)</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com">

            <div class="grid-2">
                <div>
                    <label>Visit Date</label>
                    <input type="date" name="visit_date" id="visit_date"
                           min="{{ date('Y-m-d') }}"
                           value="{{ old('visit_date', date('Y-m-d')) }}" required>
                </div>
                <div>
                    <label>Number of Guests</label>
                    <input type="number" name="guest_count" id="guest_count"
                           min="1" max="200"
                           value="{{ old('guest_count', 1) }}" required>
                </div>
            </div>

            <label>Special Notes (optional)</label>
            <textarea name="notes" rows="2" placeholder="Any special requests or notes...">{{ old('notes') }}</textarea>

            <!-- CALCULATOR -->
            <div class="calculator">
                <h4>🧮 Cost Summary</h4>
                <div class="calc-row">
                    <span>Price per person</span>
                    <span>₱{{ number_format($package['price_per_person'], 2) }}</span>
                </div>
                <div class="calc-row">
                    <span>Number of guests</span>
                    <span id="guests-display">1</span>
                </div>
                <div class="calc-row">
                    <span>Total Amount</span>
                    <span id="total-display">₱{{ number_format($package['price_per_person'], 2) }}</span>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                📋 Confirm Booking
            </button>

        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const guestInput  = document.getElementById('guest_count');
    const guestsDisp  = document.getElementById('guests-display');
    const totalDisp   = document.getElementById('total-display');
    const pricePerPerson = {{ $package['price_per_person'] }};

    function updateCalc() {
        const count = parseInt(guestInput.value) || 0;
        const total = count * pricePerPerson;
        guestsDisp.innerText = count;
        totalDisp.innerText  = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
    }

    guestInput.addEventListener('input', updateCalc);
    updateCalc();
});
</script>

</body>
</html>
