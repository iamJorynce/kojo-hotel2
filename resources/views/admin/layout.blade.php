<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Hotel System</title>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            width: 250px;
            height: 100vh;
            background: #0f172a;
            color: white;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar h2 {
            color: #38bdf8;
            margin-bottom: 25px;
        }

        .module-title {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 18px;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            margin: 5px 0;
            padding: 10px;
            border-radius: 8px;
            transition: 0.2s;
            font-size: 14px;
        }

        .sidebar a:hover {
            background: #1e293b;
            color: white;
        }

        .sidebar a.active {
            background: #0ea5e9;
            color: white;
        }

        /* MAIN */
        .main {
            margin-left: 270px;
            padding: 25px;
        }

        .card {
            background: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .btn {
            background: #0f172a;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }

        .btn-danger {
            background: #ef4444;
        }

        .logout-btn {
            width: 100%;
            background: #ef4444;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 25px;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        /* TOAST */
        #toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 14px 18px;
            border-radius: 8px;
            color: white;
            display: none;
            z-index: 9999;
            font-size: 14px;
        }
    </style>
</head>

<body>

<!-- TOAST -->
<div id="toast"></div>

<script>
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');

    toast.innerText = message;
    toast.style.display = 'block';

    toast.style.background = (type === 'success') ? '#16a34a' : '#dc2626';

    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}
</script>

@if(session('success'))
<script>
    showToast("{{ session('success') }}", 'success');
</script>
@endif

@if(session('error'))
<script>
    showToast("{{ session('error') }}", 'error');
</script>
@endif

<!-- SIDEBAR -->
<div class="sidebar">

    <h2>🏨 Admin Panel</h2>

    <div class="module-title">ADMIN</div>
    <a href="/admin/dashboard">📊 Dashboard</a>

    <div class="module-title">ROOM MANAGEMENT</div>
    <a href="/admin/rooms">🏠 Rooms</a>
    <a href="/admin/categories">🏨 Add Room Categories</a>
    <a href="/admin/rooms/create">➕ Add Room</a>

    <div class="module-title">BOOKING</div>
    <a href="/admin/bookings/create">➕ Walk-in</a>
    <a href="/admin/bookings">⏳ Bookings</a>
    <a href="/admin/booking-calendar">📅 Calendar</a>

    <div class="module-title">GUESTS</div>
    <a href="/admin/bookings/confirmed">🔵 Check-In Guest</a>
    <a href="/admin/bookings/checked-in">🔴 Check-Out Guest</a>

    <div class="module-title">ACCOUNT</div>

    <form method="GET" action="/admin/logout">
        <button class="logout-btn" onclick="return confirm('Logout now?')">
            🚪 Logout
        </button>
    </form>

</div>

<!-- MAIN -->
<div class="main">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

</body>
</html>