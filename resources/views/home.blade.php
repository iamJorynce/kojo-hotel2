<!DOCTYPE html>
<html>
<head>
    <title>Sea Eagle Beach Resort</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sand: #f5efe6;
            --sand-dark: #e8dece;
            --ocean: #0a4a6e;
            --ocean-mid: #1a7a9e;
            --ocean-light: #c8e8f5;
            --foam: #f0f9ff;
            --gold: #c9973a;
            --gold-light: #f0d49a;
            --dark: #0d1b2a;
            --text: #2c3e50;
            --text-muted: #6b7c93;
            --white: #ffffff;
            --radius: 16px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--sand);
            color: var(--text);
            overflow-x: hidden;
        }

        /* ─── NAVBAR ─── */
        .nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            padding: 18px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: transparent;
            transition: background 0.4s, backdrop-filter 0.4s, box-shadow 0.4s;
        }

        .nav.scrolled {
            background: rgba(13, 27, 42, 0.85);
            backdrop-filter: blur(14px);
            box-shadow: 0 2px 30px rgba(0,0,0,0.2);
        }

        .nav-logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 600;
            color: white;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .nav-logo span {
            display: inline-block;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--gold);
            color: white;
            font-size: 16px;
            line-height: 32px;
            text-align: center;
        }

        .nav-links { display: flex; align-items: center; gap: 30px; }

        .nav-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.5px;
            position: relative;
            transition: color 0.2s;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -3px; left: 0; right: 0;
            height: 1px;
            background: var(--gold);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s;
        }

        .nav-links a:hover { color: white; }
        .nav-links a:hover::after { transform: scaleX(1); }

        .nav-links .btn-admin {
            background: var(--gold);
            color: white !important;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 500;
        }

        .nav-links .btn-admin::after { display: none; }
        .nav-links .btn-admin:hover { background: #b8852e; }

        /* ─── HERO ─── */
        .hero {
            position: relative;
            height: 100vh;
            min-height: 650px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(10,74,110,0.55) 60%, rgba(13,27,42,0.75) 100%),
                url('{{ asset("images/hero.jpg") }}');
            background-size: cover;
            background-position: center;
            transform: scale(1.05);
            animation: bgZoom 12s ease-out forwards;
        }

        @keyframes bgZoom {
            from { transform: scale(1.05); }
            to   { transform: scale(1.0); }
        }

        /* floating particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            animation: floatUp linear infinite;
            pointer-events: none;
        }

        @keyframes floatUp {
            0%   { transform: translateY(0) translateX(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.5; }
            100% { transform: translateY(-80vh) translateX(40px); opacity: 0; }
        }

        .hero-content {
    position: relative;
    z-index: 2;
    padding: 20px;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;

    animation: fadeUp 1s 0.3s both;
}

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .hero-eyebrow {
            display: inline-block;
            color: var(--gold-light);
            font-size: 12px;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 18px;
            animation: fadeUp 1s 0.5s both;
        }

        .hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(48px, 7vw, 84px);
            font-weight: 600;
            color: white;
            line-height: 1.1;
            margin-bottom: 18px;
            animation: fadeUp 1s 0.6s both;
        }

        .hero h1 em {
            font-style: italic;
            color: var(--gold-light);
        }

        .hero p {
            font-size: 17px;
            color: rgba(255,255,255,0.8);
            max-width: 480px;
            line-height: 1.6;
            margin-bottom: 40px;
            animation: fadeUp 1s 0.75s both;
        }

        /* ─── SEARCH BOX ─── */
        .search-box {
            display: flex;
            gap: 0;
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
            animation: fadeUp 1s 0.9s both;
        }

        .search-field {
            display: flex;
            flex-direction: column;
            padding: 14px 22px;
            border-right: 1px solid #e8e8e8;
            min-width: 150px;
        }

        .search-field label {
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .search-field input {
            border: none;
            outline: none;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            background: transparent;
            width: 100%;
        }

        .search-btn {
            background: var(--ocean);
            color: white;
            border: none;
            padding: 0 28px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: background 0.2s;
            position: relative;
            overflow: hidden;
        }

        .search-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: white;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .search-btn:hover { background: var(--ocean-mid); }
        .search-btn:active { transform: scale(0.98); }

        /* ─── SCROLL INDICATOR ─── */
        .scroll-hint {
            position: absolute;
            bottom: 36px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,0.5);
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            animation: fadeUp 1s 1.3s both;
        }

        .scroll-line {
            width: 1px;
            height: 50px;
            background: linear-gradient(to bottom, rgba(255,255,255,0.5), transparent);
            animation: scrollPulse 2s ease-in-out infinite;
        }

        @keyframes scrollPulse {
            0%,100% { opacity: 0.4; transform: scaleY(1); }
            50%      { opacity: 1;   transform: scaleY(0.6); }
        }

        /* ─── SECTION ─── */
        .section { padding: 90px 20px; }

        .container {
            max-width: 1140px;
            margin: auto;
        }

        .section-label {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 12px;
            font-weight: 500;
        }

        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(34px, 4vw, 52px);
            font-weight: 600;
            color: var(--dark);
            line-height: 1.15;
            margin-bottom: 50px;
        }

        /* ─── ROOMS GRID ─── */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 28px;
        }

        /* ─── ROOM CARD ─── */
        .card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.35s;
            opacity: 0;
            transform: translateY(30px);
        }

        .card.visible {
            animation: cardReveal 0.6s forwards;
        }

        @keyframes cardReveal {
            to { opacity: 1; transform: translateY(0); }
        }

        .card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 20px 50px rgba(10,74,110,0.15);
        }

        .card-img-wrap {
            position: relative;
            overflow: hidden;
            height: 210px;
        }

        .card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s;
        }

        .card:hover img { transform: scale(1.06); }

        .card-badge {
            position: absolute;
            top: 14px; left: 14px;
            background: var(--gold);
            color: white;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .card-body { padding: 22px; }

        .card-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .card-desc {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 18px;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #f0f0f0;
            padding-top: 16px;
        }

        .card-price {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 600;
            color: var(--ocean);
        }

        .card-price sup { font-size: 14px; font-weight: 400; vertical-align: super; }
        .card-price span { font-size: 13px; color: var(--text-muted); font-family: 'DM Sans', sans-serif; font-weight: 300; }

        .card-actions { display: flex; gap: 10px; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--ocean);
            color: var(--ocean);
        }

        .btn-outline:hover { background: var(--ocean); color: white; }

        .btn-solid {
            background: var(--ocean);
            color: white;
        }

        .btn-solid:hover { background: var(--ocean-mid); transform: translateY(-1px); }

        /* ─── FEATURES ─── */
        .features-section {
            background: var(--dark);
            padding: 90px 20px;
        }

        .features-section .section-title { color: white; }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }

        .feature {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: var(--radius);
            padding: 30px 24px;
            transition: background 0.3s, transform 0.3s;
        }

        .feature:hover {
            background: rgba(255,255,255,0.09);
            transform: translateY(-4px);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(201,151,58,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
            transition: background 0.3s;
        }

        .feature:hover .feature-icon { background: rgba(201,151,58,0.35); }

        .feature-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 600;
            color: white;
            margin-bottom: 8px;
        }

        .feature-desc {
            font-size: 14px;
            color: rgba(255,255,255,0.5);
            line-height: 1.6;
        }

        /* ─── WAVES DIVIDER ─── */
        .wave-divider {
            display: block;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }

        .wave-divider svg { display: block; width: 100%; }

        /* ─── FOOTER ─── */
        .footer {
            background: var(--dark);
            color: rgba(255,255,255,0.4);
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .footer-main {
            max-width: 1140px;
            margin: auto;
            padding: 60px 20px 40px;
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr;
            gap: 40px;
        }

        .footer-brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 600;
            color: white;
            margin-bottom: 10px;
        }

        .footer-brand-desc {
            font-size: 14px;
            color: rgba(255,255,255,0.45);
            line-height: 1.65;
            max-width: 280px;
        }

        .footer-social {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .footer-social a {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 16px;
            transition: background 0.2s, color 0.2s;
        }

        .footer-social a:hover { background: var(--gold); color: white; border-color: var(--gold); }

        .footer-col-title {
            font-size: 11px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 500;
            margin-bottom: 18px;
        }

        .footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
            color: rgba(255,255,255,0.55);
            font-size: 14px;
            line-height: 1.55;
        }

        .footer-contact-item .icon { font-size: 18px; margin-top: 1px; flex-shrink: 0; }

        .footer-contact-item a {
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-contact-item a:hover { color: var(--gold-light); }

        .footer-nav a {
            display: block;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 12px;
            transition: color 0.2s, padding-left 0.2s;
        }

        .footer-nav a:hover { color: white; padding-left: 6px; }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.07);
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: rgba(255,255,255,0.25);
        }

        .footer-bottom a { color: rgba(255,255,255,0.4); text-decoration: none; }

        @media (max-width: 768px) {
            .footer-main { grid-template-columns: 1fr; gap: 32px; }
        }

        /* ─── RIPPLE ─── */
        @keyframes ripple {
            from { transform: scale(0); opacity: 0.6; }
            to   { transform: scale(4); opacity: 0; }
        }

        .ripple-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            width: 60px; height: 60px;
            pointer-events: none;
            animation: ripple 0.6s ease-out forwards;
        }
    </style>
</head>



<body>

<!-- NAV -->
<nav class="nav" id="navbar">
    <a href="/" class="nav-logo">
        <span>🦅</span>Sea Eagle Resort
    </a>
    <div class="nav-links">
        <a href="/">Home</a>
        <a href="/">About us</a>
        <a href="/rooms">Rooms</a>
        <a href="/">Contact us</a>
        <a href="/admin/login" class="btn-admin">Admin</a>
    </div>
</nav>

<!-- HERO -->
<div class="hero" id="hero">
    <div class="hero-bg"></div>

    <!-- Floating particles -->
    <div class="particle" style="width:6px;height:6px;left:15%;bottom:20%;animation-duration:8s;animation-delay:0s;"></div>
    <div class="particle" style="width:4px;height:4px;left:35%;bottom:10%;animation-duration:11s;animation-delay:2s;"></div>
    <div class="particle" style="width:8px;height:8px;left:60%;bottom:15%;animation-duration:9s;animation-delay:1s;"></div>
    <div class="particle" style="width:5px;height:5px;left:80%;bottom:25%;animation-duration:12s;animation-delay:3s;"></div>
    <div class="particle" style="width:3px;height:3px;left:50%;bottom:5%;animation-duration:10s;animation-delay:4s;"></div>

    <div class="hero-content">
        <div class="hero-eyebrow">Pindasan, Mabini, Davao de Oro</div>
        <h1>Your <em>Perfect</em><br>Beach Escape</h1>
        <p style="text-align: center;">
    Luxurious rooms, breathtaking views, and unforgettable memories await at Sea Eagle.
</p>

        <!-- SEARCH -->
         @php
        $today = date('Y-m-d');
        @endphp
        <form action="/rooms" method="GET">

    <div class="search-box" id="searchBox">

        <div class="search-field">
            <label>Check In</label>
            <input type="date"
                   name="check_in"
                   min="{{ $today }}"
                   required>
        </div>

        <div class="search-field">
            <label>Check Out</label>
            <input type="date"
                   name="check_out"
                   min="{{ $today }}"
                   required>
        </div>

        <button type="submit" class="search-btn" onclick="addRipple(this, event)">
            Check&nbsp;Availability
        </button>

        <script>
            document.addEventListener("DOMContentLoaded", function () {

                const checkIn = document.querySelector('input[name="check_in"]');
                const checkOut = document.querySelector('input[name="check_out"]');

                checkIn.addEventListener("change", function () {
                    checkOut.min = checkIn.value;
                });

            });
        </script>

        <script>
document.addEventListener("DOMContentLoaded", function () {

    const checkIn = document.querySelector('input[name="check_in"]');
    const checkOut = document.querySelector('input[name="check_out"]');

    checkIn.addEventListener("change", function () {

        // auto set minimum checkout date
        checkOut.min = checkIn.value;

        // clear invalid value
        if (checkOut.value && checkOut.value <= checkIn.value) {
            checkOut.value = "";
        }
    });

    checkOut.addEventListener("change", function () {

        if (checkOut.value <= checkIn.value) {
            alert("Check-out must be AFTER check-in date!");
            checkOut.value = "";
        }
    });

});
</script>

    </div>

</form>
    </div>

    <div class="scroll-hint">
        <div class="scroll-line"></div>
        <span>Scroll</span>
    </div>
</div>

<!-- WAVE TOP -->
<div class="wave-divider">
    <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path fill="#f5efe6" d="M0,40 C360,0 1080,80 1440,30 L1440,60 L0,60 Z"/>
    </svg>
</div>

<!-- ROOMS SECTION -->
<section class="section" style="background: var(--sand);">
    <div class="container">

        <div class="section-label">Accommodations</div>
        <div class="section-title">Featured Rooms</div>

        @foreach($categories as $category)

    <h2 style="margin-top:30px;">
        {{ $category['name'] }}
    </h2>

    @php
        $categoryRooms = collect($rooms[$category['id']] ?? []);
    @endphp

    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
        gap:20px;
        margin-top:10px;
    ">

        @foreach($categoryRooms->take(1) as $room)
            <div style="
                background:#fff;
                border-radius:14px;
                overflow:hidden;
                box-shadow:0 4px 15px rgba(0,0,0,0.08);
            ">

                <!-- IMAGE (ONLY 1 PER ROOM) -->
                <img src="{{ $room['image_url'] }}"
                     style="width:100%;height:180px;object-fit:cover;">

                <div style="padding:15px;">

                    <!-- ROOM NAME -->
                    <h3 style="margin:0;">
                        {{ $room['name'] }}
                    </h3>

                    <!-- PRICE FROM CATEGORY -->
                    <p style="color:#0a4a6e;font-weight:bold;margin:6px 0;">
                        ₱{{ number_format($category['price'], 2) }} / night
                    </p>

                    <!-- DESCRIPTION FROM CATEGORY -->
                    <p style="color:#666;font-size:13px;">
                        {{ $category['description'] ?? '' }}
                    </p>

                    <!-- BUTTON -->
                    <a href="/book-category/{{ $category['id'] }}"
                       style="
                           display:inline-block;
                           margin-top:10px;
                           padding:8px 12px;
                           background:#0a4a6e;
                           color:#fff;
                           border-radius:6px;
                           text-decoration:none;
                           font-size:13px;
                       ">
                        Book Now
                    </a>

                </div>

            </div>

        @endforeach

    </div>

@endforeach

    </div>
</section>

<!-- WAVE BEFORE FEATURES -->
<div class="wave-divider" style="background: var(--sand);">
    <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path fill="#0d1b2a" d="M0,20 C480,80 960,0 1440,40 L1440,60 L0,60 Z"/>
    </svg>
</div>

<!-- FEATURES SECTION -->
<section class="features-section">
    <div class="container">
        <div class="section-label" style="color: var(--gold-light);">Resort Highlights</div>
        <div class="section-title">Why Choose Sea Eagle</div>

        <div class="features-grid">
            <div class="feature">
                <div class="feature-icon">🏝</div>
                <div class="feature-title">Beachfront Location</div>
                <div class="feature-desc">Wake up to the sound of waves with direct access to our pristine private beach.</div>
            </div>
            <div class="feature">
                <div class="feature-icon">🛏</div>
                <div class="feature-title">Comfortable Rooms</div>
                <div class="feature-desc">Thoughtfully designed spaces that blend local craftsmanship with modern comfort.</div>
            </div>
            <div class="feature">
                <div class="feature-icon">💰</div>
                <div class="feature-title">Affordable Rates</div>
                <div class="feature-desc">Exceptional value without compromise — luxury at prices that make sense.</div>
            </div>
            <div class="feature">
                <div class="feature-icon">⭐</div>
                <div class="feature-title">24/7 Support</div>
                <div class="feature-desc">Our dedicated team is always on hand to make your stay seamless and memorable.</div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-main">

        <!-- Brand -->
        <div>
            <div class="footer-brand-name">🦅 Sea Eagle Resort</div>
            <div class="footer-brand-desc">A premier beachfront destination in Davao de Oro — where the mountains meet the sea.</div>
            <div class="footer-social">
                <a href="https://www.facebook.com/SEAEAGLEBEACHRESORTCORP" target="_blank" title="Facebook">f</a>
            </div>
        </div>

        <!-- Contact -->
        <div>
            <div class="footer-col-title">Contact Us</div>

            <div class="footer-contact-item">
                <span class="icon">📍</span>
                <span>Pindasan, Mabini,<br>Davao de Oro</span>
            </div>

            <div class="footer-contact-item">
                <span class="icon">📞</span>
                <a href="tel:+639454130470">0945 413 0470</a>
            </div>

            <div class="footer-contact-item">
                <span class="icon">✉️</span>
                <a href="mailto:seaeaglecorp@gmail.com">seaeaglecorp@gmail.com</a>
            </div>

            <div class="footer-contact-item">
                <span class="icon">📘</span>
                <a href="https://www.facebook.com/SEAEAGLEBEACHRESORTCORP" target="_blank">Sea Eagle Beach Resort Corp</a>
            </div>
        </div>

        <!-- Quick Links -->
        <div>
            <div class="footer-col-title">Quick Links</div>
            <nav class="footer-nav">
                <a href="/">Home</a>
                <a href="/rooms">Our Rooms</a>
                <a href="/admin/login">Admin Login</a>
            </nav>
        </div>

    </div>

    <div class="footer-bottom">
        © {{ date('Y') }} Sea Eagle Beach Resort Corp · Pindasan, Mabini, Davao de Oro
    </div>
</footer>

<script>
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 60);
    });

    // Scroll-triggered card reveal using IntersectionObserver
    const cards = document.querySelectorAll('.card');
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.15 });

    cards.forEach(c => obs.observe(c));

    // Features stagger on scroll
    const features = document.querySelectorAll('.feature');
    const featureObs = new IntersectionObserver((entries) => {
        entries.forEach((e, i) => {
            if (e.isIntersecting) {
                setTimeout(() => {
                    e.target.style.opacity = '1';
                    e.target.style.transform = 'translateY(0)';
                }, features && [...features].indexOf(e.target) * 80);
                featureObs.unobserve(e.target);
            }
        });
    }, { threshold: 0.2 });

    features.forEach(f => {
        f.style.opacity = '0';
        f.style.transform = 'translateY(20px)';
        f.style.transition = 'opacity 0.5s, transform 0.5s';
        featureObs.observe(f);
    });

    // Ripple button effect
    function addRipple(btn, e) {
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left - 30;
        const y = e.clientY - rect.top - 30;
        const r = document.createElement('span');
        r.className = 'ripple-circle';
        r.style.cssText = `left:${x}px;top:${y}px;`;
        btn.appendChild(r);
        setTimeout(() => r.remove(), 650);
    }
</script>

</body>
</html>