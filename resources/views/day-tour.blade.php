<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Day Tour — Sea Eagle Beach Resort</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:   #0a4a6e;
            --blue2:  #1a7a9e;
            --green:  #1a6b3c;
            --green2: #2ea05a;
            --red:    #c0392b;
            --gold:   #c9973a;
            --sand:   #f5efe6;
            --dark:   #0d1b2a;
            --white:  #ffffff;
        }

        body { font-family: 'DM Sans', sans-serif; background: var(--sand); color: #2c3e50; }

        /* NAV */
        nav {
            background: var(--dark);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav .logo {
            font-family: 'Cormorant Garamond', serif;
            color: white;
            font-size: 20px;
            font-weight: 600;
            text-decoration: none;
        }

        nav a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 14px;
            margin-left: 20px;
            transition: color 0.2s;
        }

        nav a:hover { color: white; }

        /* HERO */
        .hero {
            background:
                linear-gradient(to bottom, rgba(10,74,110,0.7), rgba(26,107,60,0.6)),
                url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1600&q=80');
            background-size: cover;
            background-position: center;
            padding: 100px 20px;
            text-align: center;
            color: white;
        }

        .hero-eyebrow {
            font-size: 12px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #f0d49a;
            margin-bottom: 16px;
        }

        .hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(40px, 6vw, 72px);
            font-weight: 600;
            line-height: 1.1;
            margin-bottom: 16px;
        }

        .hero h1 em { font-style: italic; color: #f0d49a; }

        .hero p {
            font-size: 16px;
            color: rgba(255,255,255,0.85);
            max-width: 500px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        /* PACKAGES */
        .section {
            max-width: 1000px;
            margin: 0 auto;
            padding: 70px 20px;
        }

        .section-label {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--green2);
            margin-bottom: 10px;
            font-weight: 500;
        }

        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(30px, 4vw, 48px);
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 40px;
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 28px;
        }

        .package-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .package-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(10,74,110,0.15);
        }

        .package-header {
            padding: 28px 28px 20px;
            background: linear-gradient(135deg, var(--blue), var(--green));
            color: white;
        }

        .package-header.pool {
            background: linear-gradient(135deg, var(--green), var(--blue2));
        }

        .package-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .package-price {
            font-size: 36px;
            font-weight: 700;
            color: #f0d49a;
        }

        .package-price small {
            font-size: 14px;
            font-weight: 400;
            color: rgba(255,255,255,0.7);
        }

        .package-body { padding: 24px 28px; }

        .package-body p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .inclusions { list-style: none; margin-bottom: 24px; }

        .inclusions li {
            font-size: 14px;
            color: #444;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .inclusions li::before {
            content: '✓';
            color: var(--green2);
            font-weight: bold;
        }

        .btn-book {
            display: block;
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--blue), var(--green));
            color: white;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: opacity 0.2s, transform 0.2s;
        }

        .btn-book:hover { opacity: 0.9; transform: translateY(-1px); }

        /* INFO SECTION */
        .info-section {
            background: var(--dark);
            padding: 70px 20px;
            color: white;
        }

        .info-grid {
            max-width: 1000px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .info-item .icon { font-size: 36px; margin-bottom: 12px; }
        .info-item h3 { font-family: 'Cormorant Garamond', serif; font-size: 20px; margin-bottom: 6px; }
        .info-item p { font-size: 13px; color: rgba(255,255,255,0.55); line-height: 1.6; }

        footer {
            background: var(--dark);
            border-top: 1px solid rgba(255,255,255,0.07);
            text-align: center;
            padding: 20px;
            color: rgba(255,255,255,0.3);
            font-size: 13px;
        }
    </style>
</head>
<body>

<nav>
    <a href="/" class="logo">🦅 Sea Eagle Resort</a>
    <div>
        <a href="/">Home</a>
        <a href="/rooms">Rooms</a>
        <a href="/day-tour">Day Tour</a>
        <a href="/#contact">Contact</a>
    </div>
</nav>

<!-- HERO -->
<div class="hero">
    <div class="hero-eyebrow">Pindasan, Mabini, Davao de Oro</div>
    <h1>Experience the <em>Beach</em><br>for a Day</h1>
    <p>No overnight stay needed. Bring your family and friends for a fun-filled day at Sea Eagle Beach Resort.</p>
</div>

<!-- PACKAGES -->
<section class="section">
    <div class="section-label">Day Tour Packages</div>
    <div class="section-title">Choose Your Experience</div>

    <div class="packages-grid">

        @forelse($packages as $index => $pkg)

        <div class="package-card">

            <div class="package-header {{ $index % 2 !== 0 ? 'pool' : '' }}">
                <div class="package-name">{{ $pkg['name'] }}</div>
                <div class="package-price">
                    ₱{{ number_format($pkg['price_per_person'], 2) }}
                    <small>/ person</small>
                </div>
            </div>

            <div class="package-body">

                <p>{{ $pkg['description'] ?? '' }}</p>

                @if(!empty($pkg['inclusions']))
                <ul class="inclusions">
                    @foreach(explode(',', $pkg['inclusions']) as $item)
                        <li>{{ trim($item) }}</li>
                    @endforeach
                </ul>
                @endif

                <a href="/day-tour/book/{{ $pkg['id'] }}" class="btn-book">
                    Book Now
                </a>

            </div>
        </div>

        @empty
        <p style="color:#666;">No packages available at the moment.</p>
        @endforelse

    </div>
</section>

<!-- INFO -->
<div class="info-section">
    <div class="info-grid">
        <div class="info-item">
            <div class="icon">🕗</div>
            <h3>Operating Hours</h3>
            <p>Open daily<br>7:00 AM – 6:00 PM</p>
        </div>
        <div class="info-item">
            <div class="icon">👨‍👩‍👧‍👦</div>
            <h3>Group Friendly</h3>
            <p>Perfect for families, barkadas, and team outings</p>
        </div>
        <div class="info-item">
            <div class="icon">📋</div>
            <h3>Advance Booking</h3>
            <p>Book online or walk-in on the day of your visit</p>
        </div>
        <div class="info-item">
            <div class="icon">📍</div>
            <h3>Location</h3>
            <p>Pindasan, Mabini<br>Davao de Oro</p>
        </div>
    </div>
</div>

<footer>
    © {{ date('Y') }} Sea Eagle Beach Resort Corp · Pindasan, Mabini, Davao de Oro
</footer>

</body>
</html>
