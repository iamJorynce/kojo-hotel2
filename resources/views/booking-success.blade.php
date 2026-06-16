<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3;url=/">
    <title>Booking Successful - Sea Eagle Beach Resort</title>
    <style>
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; color: #111; display: flex; flex-direction: column; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; flex: 1; display: flex; align-items: center; justify-content: center; }
        .success-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 40px; text-align: center; max-width: 500px; width: 100%; }
        .icon { font-size: 60px; margin-bottom: 20px; }
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
    <div class="success-card">
        <div class="icon">🎉</div>
        <h1 style="color: #0a4a6e; margin-bottom: 10px;">Booking Successful!</h1>
        <p style="color: #666; margin-bottom: 20px;">You will be redirected to the homepage shortly...</p>
        <a href="/" style="color: #0a4a6e; text-decoration: none; font-weight: bold;">Click here if not redirected</a>
    </div>
</main>

<!-- FOOTER -->
<footer style="background: #0a4a6e; color: white; padding: 40px 20px;">
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
    setTimeout(function() {
        window.location.href = "/";
    }, 3000);
</script>

</body>
</html>