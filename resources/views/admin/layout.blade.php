<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Sea Eagle Beach Resort</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
        }

        /* ===== BRIGHT SIDEBAR ===== */
        .sidebar {
            position: fixed;
            width: 250px;
            height: 100vh;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            color: #2c3e50;
            padding: 0;
            overflow-y: auto;
            z-index: 100;
            border-right: 2px solid #e8ecf1;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
        }

        .sidebar h2 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin: 0;
            font-size: 18px;
            border-bottom: 2px solid #e8ecf1;
            text-align: center;
        }

        .module-title {
            font-size: 11px;
            font-weight: 700;
            color: #667eea;
            padding: 14px 16px 8px 16px;
            margin: 16px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid #e8ecf1;
        }

        .sidebar a {
            display: block;
            padding: 11px 16px;
            color: #2c3e50;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.25s ease;
            margin: 0 8px;
            border-radius: 4px;
        }

        .sidebar a:hover {
            background: #f0f4ff;
            border-left-color: #667eea;
            color: #667eea;
            transform: translateX(4px);
        }

        /* ===== HIGHLIGHT UNIFIED POS ===== */
        .pos-section {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            color: #667eea;
            border-radius: 6px;
            border-left: 4px solid #667eea;
            margin: 16px 8px 8px 8px !important;
            padding: 12px 14px !important;
            border-bottom: none !important;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.08);
        }

        .sidebar a.pos-link {
            background: #f0f4ff;
            color: #667eea;
            font-weight: 600;
            border-left-color: #667eea;
            margin: 4px 8px !important;
        }

        .sidebar a.pos-link:hover {
            background: #e8ecff;
            color: #5568d3;
        }

        .logout-btn {
            width: calc(100% - 16px);
            padding: 11px 14px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.25s ease;
            margin: 8px;
            box-shadow: 0 2px 6px rgba(255, 107, 107, 0.15);
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #ff5252 0%, #ee3d3a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.25);
        }

        /* ===== MAIN CONTENT ===== */
        .main {
            margin-left: 270px;
            padding: 25px;
            min-height: 100vh;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .topbar h2 {
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
        }

        .card {
            background: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8ecf1;
        }

        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
            border: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
        }

        .btn-danger { background: #ff6b6b; }
        .btn-danger:hover { background: #ff5252; }
        .btn-success { background: #51cf66; }
        .btn-success:hover { background: #40c057; }
        .btn-warning { background: #ffd43b; color: #2c3e50; }
        .btn-warning:hover { background: #ffca3d; }
        .btn-primary { background: #667eea; }
        .btn-primary:hover { background: #5568d3; }

        /* ===== FORMS ===== */
        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        label {
            display: block;
            font-size: 13px;
            color: #555;
            margin-bottom: 6px;
            font-weight: 600;
        }

        /* ===== ALERTS ===== */
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            font-weight: 600;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #ef4444;
        }

        /* ===== SCROLLBAR ===== */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #d0d8e6;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #b8c1d6;
        }
    </style>
</head>

<body>

    <!-- TOAST NOTIFICATIONS -->
    <div id="toast"></div>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.innerText = message;
            toast.style.display = 'block';
            toast.style.background = (type === 'success') ? '#51cf66' : '#ff6b6b';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3500);
        }
    </script>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));
        </script>
    @endif

    <!-- CLEAN SIDEBAR - UNIFIED POS ONLY -->
    <div class="sidebar">
        <h2>🏨 Sea Eagle Resort</h2>

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

        <!-- BOOKINGS -->
        <div class="module-title">📅 Bookings</div>
        <a href="/admin/bookings">📅 All Bookings</a>
        <a href="/admin/booking-calendar">📅 Calendar View</a>
        <a href="/admin/bookings/confirmed">🔵 Check-In</a>
        <a href="/admin/bookings/checked-in">🔴 Check-Out</a>

        <!-- ========== ⭐ UNIFIED WALK-IN POS ⭐ ========== -->
        <div class="module-title pos-section">
            🧾 WALK-IN POS SYSTEM
        </div>
        <a href="/admin/walkin/pos" class="pos-link">➕ New Transaction</a>
        <a href="/admin/walkin/transactions" class="pos-link">📋 History</a>

        <!-- MANAGE INVENTORY -->
        <div class="module-title">📦 Manage Inventory</div>
        <a href="/admin/day-tour-packages">🏷️ Day Tour Packages</a>
        <a href="/admin/cottages">🏡 Cottages</a>
        <a href="/admin/equipment-types">🧰 Equipment</a>

        <!-- REPORTS -->
        <div class="module-title">📊 Reports</div>
        <a href="/admin/daily-summary">📈 Daily Summary</a>
        <a href="/admin/revenue-report">💰 Revenue Report</a>

        <!-- ACCOUNT -->
        <div class="module-title">⚙️ Account</div>
        <form method="GET" action="/admin/logout" style="margin: 0;">
            <button class="logout-btn" onclick="return confirm('Logout now?')">🚪 Logout</button>
        </form>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">
        @yield('content')
    </div>

    <!-- FULLCALENDAR JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

</body>

</html>