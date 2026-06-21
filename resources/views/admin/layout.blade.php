<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Sea Eagle Beach Resort</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- FIX: load FullCalendar CSS only once here (booking-calendar also loaded it, causing duplicate) --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .sidebar {
            position: fixed;
            width: 250px;
            height: 100vh;
            background: #0f172a;
            color: white;
            padding: 20px;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar h2 { color: #38bdf8; margin-bottom: 25px; }

        .module-title {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 18px;
            margin-bottom: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
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

        .sidebar a:hover { background: #1e293b; color: white; }
        .sidebar a.active { background: #0ea5e9; color: white; }

        .main {
            margin-left: 270px;
            padding: 25px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .btn {
            display: inline-block;
            background: #0f172a;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
            border: none;
        }

        .btn:hover { background: #1e293b; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #16a34a; }
        .btn-warning { background: #d97706; }
        .btn-primary { background: #0a4a6e; }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        label {
            display: block;
            font-size: 13px;
            color: #555;
            margin-bottom: 4px;
            font-weight: 500;
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
            font-size: 14px;
        }

        .logout-btn:hover { background: #dc2626; }

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
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 15px;
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
    setTimeout(() => { toast.style.display = 'none'; }, 3500);
}
</script>

@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));</script>
@endif

@if(session('error'))
<script>document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));</script>
@endif

<!-- CLEANED SIDEBAR - OLD EQUIPMENT SYSTEM REMOVED -->

<div class="sidebar">
    <h2>🏨 Sea Eagle Resort Admin</h2>

    <!-- MAIN -->
    <div class="module-title">📊 Main</div>
    <a href="/admin/dashboard">📊 Dashboard</a>
    <a href="/admin/audit-log">📋 Audit Log</a>

    <!-- STAFF & SETTINGS -->
    <div class="module-title">👥 Staff & Settings</div>
    <a href="/admin/staff">👥 Staff Management</a>
    <a href="/admin/payments">💰 Payment Records</a>
    <a href="/admin/payment-submit">💳 Submit Payment</a>

    <!-- ROOM MANAGEMENT -->
    <div class="module-title">🏠 Room Management</div>
    <a href="/admin/rooms">🏠 Rooms List</a>
    <a href="/admin/categories">🏨 Room Categories</a>
    <a href="/admin/rooms/create">➕ Add / Edit Room</a>

    <!-- STANDARD BOOKINGS -->
    <div class="module-title">🛏️ Standard Bookings</div>
    <a href="/admin/bookings/create">➕ New Booking (Walk-in)</a>
    <a href="/admin/bookings">📅 All Bookings</a>
    <a href="/admin/booking-calendar">📅 Booking Calendar</a>

    <!-- GUEST OPERATIONS -->
    <div class="module-title">🚪 Guest Operations</div>
    <a href="/admin/bookings/confirmed">🔵 Check-In Guest</a>
    <a href="/admin/bookings/checked-in">🔴 Check-Out Guest</a>

    <!-- ========== UNIFIED WALK-IN POS (NEW) ========== -->
    <div class="module-title" style="background:#38bdf8;color:white;padding:8px 14px;border-radius:6px;margin:16px 0;">
        🧾 WALK-IN POS SYSTEM (UNIFIED)
    </div>
    <a href="/admin/walkin/pos" style="color:#38bdf8;font-weight:600;font-size:14px;">➕ New Transaction</a>
    <a href="/admin/walkin/transactions" style="color:#38bdf8;">📋 Transaction History</a>

    <!-- DAY TOUR PACKAGES MANAGEMENT -->
    <div class="module-title">🌴 Day Tour Packages</div>
    <a href="/admin/day-tour-packages">🏷 Manage Packages</a>

    <!-- COTTAGE MANAGEMENT -->
    <div class="module-title">🏡 Cottage Management</div>
    <a href="/admin/cottages">🏠 Cottages</a>

    <!-- EQUIPMENT TYPES MANAGEMENT -->
    <div class="module-title">🧰 Equipment Types</div>
    <a href="/admin/equipment-types">⚙️ Manage Equipment</a>

    <!-- REPORTS -->
    <div class="module-title">📊 Reports</div>
    <a href="/admin/daily-summary">📈 Daily Summary</a>
    <a href="/admin/revenue-report">💰 Revenue Report</a>

    <!-- ACCOUNT -->
    <div class="module-title">⚙️ Account</div>
    <form method="GET" action="/admin/logout" style="margin:0;">
        <button class="logout-btn" onclick="return confirm('Logout now?')">🚪 Logout</button>
    </form>
</div>

<style>
.sidebar {
    padding: 20px 0;
}

.module-title {
    font-size: 11px;
    font-weight: 700;
    color: #666;
    padding: 12px 14px;
    margin: 20px 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e0e0e0;
}

.sidebar a {
    display: block;
    padding: 10px 14px;
    color: #333;
    text-decoration: none;
    font-size: 13px;
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.sidebar a:hover {
    background: #f5f5f5;
    border-left-color: #0a4a6e;
    color: #0a4a6e;
}

.sidebar a[href*="walkin"] {
    color: #38bdf8;
    font-weight: 500;
}

.sidebar a[href*="walkin"]:hover {
    background: #e0f7ff;
}

.logout-btn {
    width: 100%;
    padding: 10px 14px;
    background: #c0392b;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    cursor: pointer;
    text-align: left;
    font-weight: 600;
    transition: 0.2s;
}

.logout-btn:hover {
    background: #a93226;
}
</style>

<style>
.sidebar {
    padding: 20px 0;
}

.module-title {
    font-size: 11px;
    font-weight: 700;
    color: #666;
    padding: 12px 14px;
    margin: 20px 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e0e0e0;
}

.sidebar a {
    display: block;
    padding: 10px 14px;
    color: #333;
    text-decoration: none;
    font-size: 13px;
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.sidebar a:hover {
    background: #f5f5f5;
    border-left-color: #0a4a6e;
    color: #0a4a6e;
}

.sidebar a[href*="walkin"] {
    color: #38bdf8;
    font-weight: 500;
}

.sidebar a[href*="walkin"]:hover {
    background: #e0f7ff;
}

.logout-btn {
    width: 100%;
    padding: 10px 14px;
    background: #c0392b;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    cursor: pointer;
    text-align: left;
    font-weight: 600;
    transition: 0.2s;
}

.logout-btn:hover {
    background: #a93226;
}
</style>

<style>
.sidebar {
    padding: 20px 0;
}

.module-title {
    font-size: 11px;
    font-weight: 700;
    color: #666;
    padding: 12px 14px;
    margin: 20px 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e0e0e0;
}

.sidebar a {
    display: block;
    padding: 10px 14px;
    color: #333;
    text-decoration: none;
    font-size: 13px;
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.sidebar a:hover {
    background: #f5f5f5;
    border-left-color: #0a4a6e;
    color: #0a4a6e;
}

.sidebar a[href*="walkin"] {
    color: #38bdf8;
    font-weight: 500;
}

.sidebar a[href*="walkin"]:hover {
    background: #e0f7ff;
}

.logout-btn {
    width: 100%;
    padding: 10px 14px;
    background: #c0392b;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    cursor: pointer;
    text-align: left;
    font-weight: 600;
    transition: 0.2s;
}

.logout-btn:hover {
    background: #a93226;
}
</style>


<!-- MAIN CONTENT -->
<div class="main">
    @yield('content')
</div>

{{-- FIX: FullCalendar JS loaded once here at the bottom --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

</body>
</html>
