<!DOCTYPE html>
<html>
<head>
    <title>Room Details</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; margin:0; }
        .container { max-width:800px; margin:50px auto; background:white; padding:20px; border-radius:10px; }
        .price { color:green; font-size:20px; font-weight:bold; }
        a { text-decoration:none; color:blue; }
    </style>
</head>
<body>

<div class="container">

    <a href="/rooms">← Back to Rooms</a>

    <h1>{{ $room['name'] ?? 'Room' }}</h1>

    <p>{{ $room['description'] ?? 'No description available' }}</p>

    <p class="price">₱{{ $room['price'] ?? '0' }} per night</p>

    <hr>

    <h3>Amenities</h3>
    <p>{{ $room['amenities'] ?? 'WiFi, Aircon, TV' }}</p>

    <a href="/book/{{ $room['id'] }}">
    <button style="padding:10px 20px; background:green; color:white; border:none; border-radius:5px;">
        Book Now
    </button>
    </a>

</div>

</body>
</html>