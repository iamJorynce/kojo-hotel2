<!DOCTYPE html>
<html>
<head>
    <title>{{ $room['name'] }} - Kojo Hotel</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        /* NAV */
        .nav {
            background: #111;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
        }

        .nav a {
            color: white;
            margin-left: 15px;
            text-decoration: none;
        }

        /* CONTAINER */
        .container {
            max-width: 1000px;
            margin: auto;
            padding: 20px;
        }

        /* CARD */
        .card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .content {
            padding: 20px;
        }

        .price {
            color: green;
            font-size: 22px;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background: #111;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .back {
            margin-top: 10px;
            display: inline-block;
            color: #555;
        }
    </style>
</head>
<body>

<!-- NAV -->
<div class="nav">
    <div><b>Kojo Hotel</b></div>

    <div>
        <a href="/">Home</a>
        <a href="/rooms">Rooms</a>
        <a href="/admin/login">Admin</a>
    </div>
</div>

<!-- CONTENT -->
<div class="container">

    <div class="card">

        <img src="{{ $room['image_url'] ?? 'https://via.placeholder.com/600' }}">

        <div class="content">

            <h1>{{ $room['name'] }}</h1>

            <p class="price">₱{{ $room['price'] }}</p>

            <p>{{ $room['description'] }}</p>

            <!-- BOOK BUTTON (next step nato ni i-connect) -->
            <a href="/book/{{ $room['id'] }}" class="btn">
                Book Now
            </a>

            <br>

            <a href="/rooms" class="back">← Back to Rooms</a>

        </div>

    </div>

</div>

</body>
</html>