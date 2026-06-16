<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Rooms - Sea Eagle Beach Resort</title>
    <style>
        /* GLOBAL STYLES */
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; color: #111; }
        a { text-decoration: none; transition: 0.2s; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        /* HEADER */
        header { background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
        .nav-container { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; }
        .logo { color: #0a4a6e; font-size: 22px; font-weight: bold; }
        .nav-links { display: flex; gap: 20px; }
        .nav-links a { color: #333; font-weight: 500; }
        .nav-links a:hover, .nav-links a.active { color: #0a4a6e; font-weight: bold; }

        /* MAIN CONTENT */
        .main-content { padding: 40px 0; }
        .section-title { text-align: center; color: #0a4a6e; margin-bottom: 30px; font-size: 28px; }
        
        .date-banner {
            background: #eaf4ff; color: #0a4a6e; padding: 15px; border-radius: 10px; 
            text-align: center; margin-bottom: 25px; font-weight: bold;
        }

        /* ROOM CARDS */
        .rooms-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
        .room-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .room-card:hover { transform: translateY(-5px); }
        .room-image { height: 220px; overflow: hidden; background: #f0f0f0; }
        .room-image img { width: 100%; height: 100%; object-fit: cover; }
        .room-info { padding: 20px; }
        .room-info h3 { margin: 0 0 10px; color: #111; font-size: 20px; }
        .room-price { color: #0a4a6e; font-size: 18px; font-weight: bold; margin: 10px 0; }
        .room-price span { font-size: 14px; color: #666; font-weight: normal; }
        .room-desc { color: #666; font-size: 14px; line-height: 1.5; margin: 15px 0; }
        .btn-primary {
            display: block; text-align: center; background: #0a4a6e; color: white; 
            padding: 12px; border-radius: 8px; font-weight: bold; margin-top: 15px;
        }
        .btn-primary:hover { background: #0d6efd; }
        .btn-disabled {
            display: block; text-align: center; background: #ccc; color: #666; 
            padding: 12px; border-radius: 8px; font-weight: bold; margin-top: 15px; cursor: not-allowed;
        }
        .badge {
            background: #0a4a6e; color: white; padding: 4px 10px; border-radius: 20px; 
            font-size: 12px; font-weight: bold; display: inline-block; margin-bottom: 10px;
        }
        .available-text { color: #2f855a; font-size: 14px; font-weight: bold; margin-bottom: 15px; }
        .booked-text { color: #c62828; font-size: 14px; font-weight: bold; margin-bottom: 15px; }

        /* FOOTER */
        footer { background: #0a4a6e; color: white; padding: 40px 0; margin-top: 60px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
        .footer-grid h3, .footer-grid h4 { margin-top: 0; }
        .footer-grid p, .footer-grid a { color: #cbd5e1; font-size: 14px; margin: 5px 0; display: block; }
        .footer-grid a:hover { color: white; }
        .copyright { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); color: #cbd5e1; font-size: 13px; }
    </style>
    
</head>
<body>

<!-- HEADER -->
<header>
    <div class="container nav-container">
        <a href="/" class="logo">🦅 Sea Eagle Resort</a>
        <nav class="nav-links">
            <a href="/">Home</a>
            <a href="/rooms" class="active">Rooms</a>
            <a href="/#contact">Contact us</a>
            <a href="/admin/login">Admin</a>
        </nav>
    </div>
</header>

<!-- MAIN CONTENT -->
<main class="main-content container">
    <h2 class="section-title">Our Rooms</h2>

    {{-- Display the check-in/check-out dates submitted from homepage search --}}
    @if(request('check_in') && request('check_out'))
        <div class="date-banner">
            Showing availability from 
            {{ \Carbon\Carbon::parse(request('check_in'))->format('M d, Y') }} 
            to 
            {{ \Carbon\Carbon::parse(request('check_out'))->format('M d, Y') }}
        </div>
    @else
        <p style="text-align: center; color: #666; margin-bottom: 25px;">Browse all available room categories below.</p>
    @endif

    <div class="rooms-grid">
        @foreach($categories as $category)
            @php
                $filteredRooms  = $rooms->where('category_id', $category['id']);
                $availableRooms = $filteredRooms->where('status', 'available');
                $sampleRoom     = $filteredRooms->first();
            @endphp

            @if($sampleRoom)
                <div class="room-card">
                    
                    <!-- ✅ PICTURE NA DIRI (Gikan sa $sampleRoom kay naa sa rooms table ang image_url) -->
                    <div class="room-image">
                        <img src="{{ $sampleRoom['image_url'] ?? 'https://via.placeholder.com/400x250?text=Sea+Eagle+Resort' }}" 
                             alt="{{ $category['name'] }}">
                    </div>

                    <div class="room-info">
                        <span class="badge">🏷 {{ $category['name'] }}</span>
                        <h3>{{ $category['name'] }}</h3>
                        <p class="room-desc">{{ $category['description'] ?? 'No description available.' }}</p>
                        
                        <p class="room-price">
                            ₱{{ number_format($category['price'], 2) }} 
                            <span>/ night</span>
                        </p>

                        @if($availableRooms->count() > 0)
                            <p class="available-text">✓ {{ $availableRooms->count() }} room(s) available</p>
                            <a href="/book-category/{{ $category['id'] }}{{ request('check_in') ? '?check_in='.request('check_in').'&check_out='.request('check_out') : '' }}" 
                               class="btn-primary">
                                Book Now
                            </a>
                        @else
                            <p class="booked-text">✗ Fully booked</p>
                            <span class="btn-disabled">Fully Booked</span>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</main>

<!-- FOOTER -->
<footer id="contact">
    <div class="container footer-grid">
        <div>
            <h3>🦅 Sea Eagle Resort</h3>
            <p>A premier beachfront destination in Davao de Oro — where the mountains meet the sea.</p>
        </div>
        <div>
            <h4>Contact Us</h4>
            <p>📍 Pindasan, Mabini, Davao de Oro</p>
            <p>📞 <a href="tel:+639454130470">0945 413 0470</a></p>
            <p>✉️ <a href="mailto:seaeaglecorp@gmail.com">seaeaglecorp@gmail.com</a></p>
            <p>📘 <a href="https://www.facebook.com/SEAEAGLEBEACHRESORTCORP" target="_blank">Sea Eagle Beach Resort Corp</a></p>
        </div>
        <div>
            <h4>Quick Links</h4>
            <a href="/">Home</a>
            <a href="/rooms">Our Rooms</a>
            <a href="/admin/login">Admin Login</a>
        </div>
    </div>
    <div class="container copyright">
        © {{ date('Y') }} Sea Eagle Beach Resort Corp. All rights reserved.
    </div>
</footer>

</body>
</html>