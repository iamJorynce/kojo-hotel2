<!DOCTYPE html>
<html>
<head>
    <title>Book Room</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            width: 450px;
            margin: 60px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .room-info {
            text-align: center;
            margin-bottom: 20px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        label {
            font-size: 13px;
            color: #333;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #111;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #333;
        }

        .error {
            background: #e74c3c;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            text-align: center;
        }

        .success {
            background: #2ecc71;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            text-align: center;
        }

        .back {
            text-align: center;
            margin-top: 10px;
        }

        .back a {
            color: #555;
            text-decoration: none;
        }

        .back a:hover {
            color: #000;
        }
    </style>

</head>
<body>
@php
$price = $room['price'] ?? 0;
$dp = $room['downpayment'] ?? ($price * 0.5);
$balance = $room['balance'] ?? ($price - $dp);
@endphp

<div class="container">

    <h2>Book Room</h2>

    <div class="room-info">
        <strong>{{ $room['name'] }}</strong><br>
        ₱{{ $room['price'] ?? '' }}
    </div>

    <!-- ERROR -->
    @if(session('error'))
        <div class="error">
            {{ session('error') }}
        </div>
    @endif

    <!-- SUCCESS -->
    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    <!-- FORM -->
    <form method="POST" action="/book/{{ $room['uuid_id'] }}">
    @csrf

        <label>Full Name</label>
        <input type="text" name="full_name" placeholder="Enter your name" required>

        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="Enter your phone number" required>

        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required>

        @php
        $today = date('Y-m-d');
        @endphp

<label>Check-in</label>
<input type="date"
       name="check_in"
       value="{{ request('check_in') }}"
       min="{{ $today }}"
       required>

<label>Check-out</label>
<input type="date"
       name="check_out"
       value="{{ request('check_out') }}"
       min="{{ $today }}"
       required>

        <button type="submit">
            Confirm Booking
        </button>
    </form>

    <div class="back">
        <a href="/rooms">← Back to Rooms</a>
    </div>

</div>

</body>
</html>