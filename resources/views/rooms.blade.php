<!DOCTYPE html>
<html>
<head>
    <title>Rooms - Sea Eagle Beach Resort</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5efe6;
            margin: 0;
        }

        /* NAV */
       .nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 70px;

    background: #0d1b2a;
    color: white;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 30px;
    box-sizing: border-box;

    z-index: 1000;
}
/* NAV LINKS */
.nav-links {
    display: flex;
    align-items: center;
    gap: 20px;
    white-space: nowrap; /* 🔥 IMPORTANT */
}

.nav a:hover {
    background: rgba(255,255,255,0.1);
}

      .nav a {
    color: white;
    text-decoration: none;
    font-size: 14px;
    padding: 8px 10px;
    border-radius: 6px;
    transition: 0.2s;
}

        /* CONTAINER */
        .container {
    max-width: 1100px;
    margin: auto;
    padding: 140px 20px 60px; /* 🔥 FIX: top padding increased */
}

        /* CATEGORY TITLE */
        .category-title {
            margin-top: 30px;
            padding: 12px 15px;
            background: #0a4a6e;
            color: white;
            border-radius: 10px;
            font-weight: 500;
        }

        /* GRID */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        /* CARD */
        .room-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0,0,0,0.08);
            transition: 0.2s;
        }

        .room-card:hover {
            transform: translateY(-3px);
        }

        .room-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .room-body {
            padding: 15px;
        }

        .price {
            font-size: 18px;
            font-weight: bold;
            color: #0a4a6e;
        }

        .available {
            color: green;
            font-size: 13px;
        }

        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 9px 14px;
            background: #0077b6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn.disabled {
            background: gray;
            pointer-events: none;
        }
    </style>
</head>

<body>

<!-- NAV -->
<div class="nav">
    <div>🦅 Sea Eagle Resort</div>
    <div>
        <a href="/">Home</a>
        <a href="/rooms">Rooms</a>
        <a href="/">Contact us</a>
        <a href="/admin/login" class="btn-admin">Admin</a>
    </div>
</div>

<div class="container">

    <h2>Room Categories</h2>

    @foreach($categories as $category)

        @php
            $filteredRooms = $rooms->where('category_id', $category['id']);
            $availableRooms = $filteredRooms->where('status', 'available');
            $sampleRoom = $filteredRooms->first();
        @endphp

        <div class="category-title">
            🏷 {{ $category['name'] }}
        </div>

        <div class="grid">

            @if($sampleRoom)

                <div class="room-card">

                    <img src="{{ $sampleRoom['image_url'] ?? 'https://via.placeholder.com/300' }}"
                         class="room-img">

                    <div class="room-body">

                        <h3>{{ $category['name'] }}</h3>

                        <p>{{ $category['description'] ?? 'No description available' }}</p>

                        <div class="price">
                            ₱{{ number_format($category['price'], 2) }}
                        </div>

                        <p class="available">
                            {{ $availableRooms->count() }} Available Room(s)
                        </p>

                        @if($availableRooms->count() > 0)
                            <a href="/book-category/{{ $category['id'] }}" class="btn">
                                Book Now
                            </a>
                        @else
                            <span class="btn disabled">Fully Booked</span>
                        @endif

                    </div>

                </div>

            @endif

        </div>

    @endforeach

</div>

</body>
</html>