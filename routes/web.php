<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\RoomController;
use App\Services\SupabaseService;


/*
|--------------------------------------------------------------------------
| PUBLIC PAGES
|--------------------------------------------------------------------------
*/

Route::get('/', function (SupabaseService $supabase) {

    $rooms = collect($supabase->getRooms())
        ->groupBy('category_id');

    $categories = $supabase->getRoomCategories();

    return view('home', compact('rooms', 'categories'));
});


Route::get('/rooms', function (Request $request, SupabaseService $supabase) {

    $rooms = collect($supabase->getRooms());
    $categories = collect($supabase->getRoomCategories());

    $checkIn = $request->check_in;
    $checkOut = $request->check_out;

    // 🔥 MAP ROOMS CLEANLY
    $rooms = $rooms->map(function ($room) use ($supabase, $checkIn, $checkOut, $categories) {

        $available = true;
        $bookedUntil = null;

        // availability check
        if ($checkIn && $checkOut) {

            $result = $supabase->checkAvailability(
                $room['uuid_id'],
                $checkIn,
                $checkOut
            );

            $available = $result['available'] ?? true;
            $bookedUntil = $result['booked_until'] ?? null;
        }

        // 🔥 CATEGORY PRICE FIX (IMPORTANT)
        $category = $categories->firstWhere('id', $room['category_id']);

        $room['price'] = $category['price'] ?? 0;
        $room['category_name'] = $category['name'] ?? 'N/A';

        // availability fields
        $room['available'] = $available;
        $room['booked_until'] = $bookedUntil;

        return $room;
    });

    return view('rooms', compact(
        'rooms',
        'categories',
        'checkIn',
        'checkOut'
    ));
});





/*
|--------------------------------------------------------------------------
| ADMIN LOGIN
|--------------------------------------------------------------------------
*/  

Route::get('/admin/login', function () {
    return view('admin.login');
});

Route::post('/admin/login', function (Request $request, SupabaseService $auth) {

    $user = $auth->login($request->email, $request->password);

    if (!$user) {
        return back()->with('error', 'Invalid credentials');
    }

    session([
        'admin_logged_in' => true,
        'admin_id' => $user['id'],
        'admin_role' => $user['role']
    ]);

    return redirect('/admin/rooms');
});



Route::get('/admin/logout', function () {

    session()->flush();
    session()->regenerateToken();

    return redirect('/admin/login');
});


/*
|--------------------------------------------------------------------------
| ADMIN ROOMS
|--------------------------------------------------------------------------
*/

Route::get('/admin/rooms', function (SupabaseService $supabase) {

    $rooms = $supabase->getRooms();

    $categories = collect(
        $supabase->getRoomCategories()
    );

    return view('admin.rooms', compact(
        'rooms',
        'categories'
    ));
});




Route::get('/admin/rooms/create', function (SupabaseService $supabase) {

    $rooms = collect($supabase->getRooms());
    $categories = collect($supabase->getRoomCategories());

    return view('admin.rooms-create', compact('rooms', 'categories'));
});

Route::post('/admin/rooms/create', function (Illuminate\Http\Request $request, SupabaseService $supabase) {

    $categories = collect($supabase->getRoomCategories());

    $category = $categories->firstWhere('id', $request->category_id);

    $roomName = ($category['name'] ?? 'Room');

    $response = $supabase->createRoom([
        'name' => $roomName,
        'room_number' => trim($request->room_number),
        'category_id' => $request->category_id,
        'status' => 'available',
        'image_url' => $request->image_url ?? null
    ]);

    if (!$response) {
        return back()->with('error', 'Failed to create room');
    }

    return back()->with('success', 'Room created successfully');

});



Route::get('/admin/rooms/delete/{id}', function ($id, SupabaseService $supabase) {

    $response = $supabase->deleteRoom($id);

    if (!$response) {
        return back()->with('error', 'Failed to delete room');
    }

    return back()->with('success', 'Room deleted successfully');
});

Route::get('/admin/rooms/edit/{id}', function ($id, SupabaseService $supabase) {

    $rooms = collect($supabase->getRooms());
    $categories = collect($supabase->getRoomCategories());

    $room = $rooms->firstWhere('id', $id);

    if (!$room) {
        return back()->with('error', 'Room not found');
    }

    return view('admin.rooms-edit', compact('room', 'categories'));
});


Route::post('/admin/rooms', function (Request $request, SupabaseService $supabase) {

    $supabase->createRoom([
        'room_number' => $request->room_number,
        'name' => $request->name,
        'price' => $request->price,
        'description' => $request->description ?? '',
        'image_url' => $request->image_url ?? '',
        'status' => 'available',

        // 🔥 IMPORTANT
        'category_id' => $request->category_id,
    ]);

    return redirect('/admin/rooms')->with('success', 'Room added successfully');
});


Route::get('/admin/rooms/delete/{id}', function ($id, SupabaseService $supabase) {

    $supabase->deleteRoom($id);

    return back()->with('success', 'Room deleted successfully');
});


/*
|--------------------------------------------------------------------------
| ADMIN BOOKINGS
|--------------------------------------------------------------------------
*/

Route::get('/admin/bookings', function (Request $request, SupabaseService $supabase) {

    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }

    $status = $request->get('status', 'all');

    $bookings = collect($supabase->getBookings());

    // 🔥 HERE IBUTANG ANG CONFLICT LOGIC
   $bookings = collect($bookings)->map(function ($b) use ($bookings) {

    $b['has_conflict'] = $bookings->contains(function ($x) use ($b) {

            // 🚨 SKIP SELF (IMPORTANT FIX)
            if ($x['id'] === $b['id']) {
            return false;
            }
        
            if (($x['room_uuid'] ?? null) !== $b['room_uuid']) {
                return false;
            }

            if (!in_array($x['status'], ['confirmed', 'checked_in'])) {
                return false;
            }

            return (
                $x['check_in'] < $b['check_out'] &&
                $x['check_out'] > $b['check_in']
            );
        });

        return $b;
    });

    if ($status !== 'all') {
        $bookings = $bookings->where('status', $status);
    }

    return view('admin.bookings', [
        'bookings' => $bookings,
        'status' => $status
    ]);
});

Route::get('/admin/bookings/confirmed', function () {

    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }

    return app(App\Http\Controllers\RoomController::class)
        ->confirmedBookings();
});

Route::get('/admin/bookings/cancel/{id}', function ($id, SupabaseService $supabase) {

    $booking = $supabase->getBookingById($id);

    if (!$booking) {
        return back()->with('error', 'Booking not found');
    }

    // 🚨 SAFETY RULE
    if ($booking['status'] === 'checked_in') {
        return back()->with('error', 'Cannot cancel checked-in booking');
    }

    if ($booking['payment_status'] === 'paid') {
        return back()->with('error', 'Cannot cancel paid booking');
    }

    $supabase->updateBooking($id, [
        'status' => 'cancelled'
    ]);

    return back()->with('success', 'Booking cancelled successfully');
});


Route::get('/admin/bookings/confirm/{id}', function ($id, SupabaseService $supabase) {

    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }

    $booking = collect($supabase->getBookings())
        ->firstWhere('id', $id);

    if (!$booking) {
        return back()->with('error', 'Booking not found');
    }

    // 🧠 STEP 1 — CHECK CONFLICT (IBUTANG DRI)
    $bookings = collect($supabase->getBookings());

    $conflict = $bookings->contains(function ($b) use ($booking) {

        if ($b['room_uuid'] !== $booking['room_uuid']) {
            return false;
        }

        if (!in_array($b['status'], ['confirmed', 'checked_in'])) {
            return false;
        }

        return (
            $b['check_in'] < $booking['check_out'] &&
            $b['check_out'] > $booking['check_in']
        );
    });

    if ($conflict) {
        return back()->with('error', 'Room already reserved for these dates.');
    }

    // 🚀 STEP 2 — CONFIRM ONLY IF SAFE
    $supabase->updateBooking($id, [
        'status' => 'confirmed'
    ]);

    $supabase->syncRoomStatus($booking['room_uuid'], 'reserved');

    $supabase->updateRoom($booking['room_uuid'], [
        'status' => 'reserved'
    ]);

    return back()->with('success', 'Booking confirmed');
});





/*
|--------------------------------------------------------------------------
| BOOKING SUCCESS PAGE
|--------------------------------------------------------------------------
*/

Route::get('/booking-success', function () {
    return view('booking-success');
});
/*
|--------------------------------------------------------------------------
| EDIT ROOMS
|--------------------------------------------------------------------------
*/


Route::post('/admin/rooms/update/{id}', function ($id, Illuminate\Http\Request $request, SupabaseService $supabase) {

    $response = $supabase->updateRoom($id, [
        'room_number' => $request->room_number,
        'category_id' => $request->category_id, // 💥 IMPORTANT
        'status' => $request->status
    ]);

    if (!$response) {
   
}

    return redirect('/admin/rooms')
        ->with('success', 'Room updated successfully');

});
/*
|--------------------------------------------------------------------------
| calendar
|--------------------------------------------------------------------------
*/
Route::get('/admin/rooms/{id}/calendar-data', function ($id, App\Services\SupabaseService $supabase) {

    $rooms = $supabase->getRooms();
    $room = collect($rooms)->firstWhere('id', $id);

    if (!$room) abort(404);

    $bookings = $supabase->getBookings();

    $events = []; // 🔥 IMPORTANT INIT

    foreach ($bookings as $b) {

        // FILTER ONLY THIS ROOM
        if (($b['room_uuid'] ?? null) !== $room['uuid_id']) {
            continue;
        }

        // COLOR MAP
        $color = match($b['status'] ?? 'pending') {
            'confirmed' => 'green',
            'pending' => 'orange',
            'checked_in' => 'blue',
            'cancelled' => 'red',
            default => 'gray'
        };

        $events[] = [
            'title' => ($b['full_name'] ?? 'Guest') . ' - ' . ($b['room_name'] ?? '').' - Room ' . ($b['room_number'] ?? ''),

            'start' => $b['check_in'],

            // 🔥 FIX: FullCalendar end is exclusive
            'end' => date('Y-m-d', strtotime($b['check_out'] . ' +1 day')),

            'color' => $color,
        ];
    }

    return response()->json($events);
});
Route::get('/admin/rooms/calendar/{id}', function ($id, App\Services\SupabaseService $supabase) {

    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }

    $rooms = $supabase->getRooms();
    $room = collect($rooms)->firstWhere('id', $id);

    if (!$room) {
        abort(404);
    }

    return view('admin.room-calendar', compact('room'));
});
/*
|--------------------------------------------------------------------------
| PUBLIC PAGES
|--------------------------------------------------------------------------
*/


Route::post('/book/{uuid}', function ($uuid, Request $request, SupabaseService $supabase) {

    // 1. FIND ROOM
    $room = collect($supabase->getRooms())
        ->firstWhere('uuid_id', $uuid);

    if (!$room) {
        return back()->with('error', 'Room not found');
    }

    $roomUuid = $room['uuid_id'];

    // 2. DATE VALIDATION
    if ($request->check_in < date('Y-m-d')) {
        return back()->with('error', 'Invalid check-in date');
    }

    if ($request->check_out <= $request->check_in) {
        return back()->with('error', 'Invalid date range');
    }

    // 3. FINAL AVAILABILITY CHECK
    if (!$supabase->isRoomAvailable(
        $roomUuid,
        $request->check_in,
        $request->check_out
    )) {
        return back()->with('error', 'Room already booked');
    }

    // 4. PRICE CALCULATION
    $category = collect($supabase->getRoomCategories())
        ->firstWhere('id', $room['category_id']);

    $pricePerNight = $category['price'] ?? 0;

    $checkIn = new DateTime($request->check_in);
    $checkOut = new DateTime($request->check_out);

    $nights = $checkIn->diff($checkOut)->days;

    $total = $pricePerNight * $nights;

    // 💥 IMPORTANT FIX (NO DP HERE)
    $paid_amount = 0;
    $balance_amount = $total;
    $payment_status = 'unpaid';

    // 5. CREATE BOOKING
    $response = $supabase->createBooking([
        'room_uuid' => $room['uuid_id'],
        'room_name' => $room['name'],
        'room_number' => $room['room_number'] ?? null,
        'room_price' => $pricePerNight,

        'total_amount' => $total,
        'paid_amount' => $paid_amount,
        'balance_amount' => $balance_amount,

        'full_name' => $request->full_name,
        'phone' => $request->phone,
        'email' => $request->email,

        'check_in' => $request->check_in,
        'check_out' => $request->check_out,

        'nights' => $nights,

        'status' => 'pending',
        'payment_status' => $payment_status,
    ]);

    if (!$response) {
        return back()->with('error', 'Booking failed');
    }

    return redirect('/booking-success')
        ->with('success', 'Booking successful');
});
/*
|--------------------------------------------------------------------------
| para ni sa viewing 
|--------------------------------------------------------------------------
*/
Route::get('/admin/bookings/create', function (SupabaseService $supabase) {

    $rooms = $supabase->getRooms();
    $categories = $supabase->getRoomCategories();

    return view('admin.bookings-create', compact('rooms', 'categories'));
});
/*
|--------------------------------------------------------------------------
| para pag fetch
|--------------------------------------------------------------------------
*/
Route::post('/admin/bookings/create', function (
    Illuminate\Http\Request $request,
    App\Services\SupabaseService $supabase
) {

    // 📍 GET ROOM
    $roomId = $request->room_id;

    $room = collect($supabase->getRooms())
        ->firstWhere('uuid_id', $roomId);

    if (!$room) {
        return back()->with('error', 'Room not found');
    }

    // 📍 CATEGORY
    $category = collect($supabase->getRoomCategories())
        ->firstWhere('id', $room['category_id']);

    $pricePerNight = $category['price'] ?? 0;

    $pricePerNight = (float) $category['price'];
    $nights = $checkIn->diff($checkOut)->days;

    $total = $pricePerNight * $nights;

if ($pricePerNight <= 0) {
    return back()->with('error', 'Invalid room price');
}

    // 📍 DATES
    $checkIn = new DateTime($request->check_in);
    $checkOut = new DateTime($request->check_out);

    $nights = $checkIn->diff($checkOut)->days;

    if ($nights <= 0) {
        return back()->with('error', 'Invalid number of nights');
    }

    // 📍 TOTAL CALCULATION
    $total = $pricePerNight * $nights;

    // 📍 AVAILABILITY CHECK (ONLY ONCE)
    $availability = $supabase->checkAvailability(
        $room['uuid_id'],
        $request->check_in,
        $request->check_out
    );

    if (!($availability['available'] ?? false)) {
        return back()->with('error', 'Room already booked');
    }

    // 📍 CASH INPUT
    $cashReceived = (float) $request->cash_received;

    // 📍 PAYMENT LOGIC (FIXED CORE ISSUE)
    $cashReceived = (float) $request->cash_received;

if ($cashReceived < 0) {
    return back()->with('error', 'Invalid payment amount');
}

// PARTIAL OR FULL PAYMENT SUPPORT
if ($cashReceived >= $total) {

    // FULL PAYMENT
    $payment_status = 'paid';
    $paid_amount = $total;
    $balance = 0;

} else {

    // PARTIAL PAYMENT
    if ($cashReceived <= 0) {
        return back()->with('error', 'Partial payment requires amount');
    }

    $payment_status = 'partial';
    $paid_amount = $cashReceived;
    $balance = $total - $cashReceived;
}

    // 📍 CREATE BOOKING (FIXED MAPPING)
    $response = $supabase->createBooking([
        'room_uuid' => $room['uuid_id'],
        'room_name' => $room['name'] ?? '',
        'room_number' => $room['room_number'] ?? '',
        'room_price' => $pricePerNight,
        'room_description' => $room['description'] ?? '',

        'full_name' => $request->full_name,
        'phone' => $request->phone,
        'email' => $request->email,

        'check_in' => $request->check_in,
        'check_out' => $request->check_out,
        'nights' => $nights,

        'status' => 'confirmed',

        // 💥 FIXED PAYMENT FIELDS
        'payment_status' => $payment_status,
        'total_amount' => $total,
        'paid_amount' => $paid_amount,
        'balance_amount' => $balance,
    ]);

    if (!$response) {
        return back()->with('error', 'Booking failed');
    }

    // 📍 UPDATE ROOM STATUS
    $supabase->updateRoom($room['uuid_id'], [
        'status' => 'reserved'
    ]);

    return redirect('/admin/bookings/confirmed')
        ->with('success', 'Booking created successfully');
});


/*
|--------------------------------------------------------------------------
| BOOKING CALENDAR MODULE
|--------------------------------------------------------------------------
*/

Route::get('/admin/booking-calendar', function (App\Services\SupabaseService $supabase) {

    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }

    $bookings = $supabase->getBookings();
    $rooms = collect($supabase->getRooms());

    $events = [];

    foreach ($bookings as $b) {

        $room = $rooms->firstWhere('uuid_id', $b['room_uuid']);

        $events[] = [
             'title' => ($b['full_name'] ?? 'Guest') . ' - ' . ($b['room_name'] ?? '').' - Room ' . ($b['room_number'] ?? ''),
            'start' => $b['check_in'],
            'end' => date('Y-m-d', strtotime($b['check_out'] . ' +1 day')),

            'color' => match($b['status'] ?? 'pending') {
                'pending' => 'orange',
                'confirmed' => 'green',
                'checked_in' => 'blue',
                'cancelled' => 'red',
                default => 'gray'
            },

            // 👇 IMPORTANT DATA FOR CLICK
            'extendedProps' => [
                'guest' => $b['full_name'] ?? '-',
                'phone' => $b['phone'] ?? '-',
                'room_number' => $room['room_number'] ?? '-',
                'room_type' => $room['name'] ?? '-',
                'check_in' => $b['check_in'] ?? '-',
                'check_out' => $b['check_out'] ?? '-',
                'status' => $b['status'] ?? '-',
            ]
        ];
    }

    return view('admin.booking-calendar', compact('events'));
});


/*
|--------------------------------------------------------------------------
| CHECKIN ROUTE
|--------------------------------------------------------------------------
*/

Route::get('/admin/bookings/checkin/{id}', function ($id, SupabaseService $supabase) {

    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }

    $booking = collect($supabase->getBookings())
        ->firstWhere('id', $id);

    if (!$booking) {
        return back()->with('error', 'Booking not found');
    }

    // 💥 STRICT PAYMENT RULE (IMPORTANT FIX)
    if (
        ($booking['payment_status'] ?? '') !== 'paid' ||
        ($booking['balance_amount'] ?? 0) > 0
    ) {
        return back()->with('error', 'Full payment required before check-in');
    }

    // check-in update
    $supabase->updateBooking($id, [
        'status' => 'checked_in'
    ]);

    return back()->with('success', 'Guest checked in');
});
/*
|--------------------------------------------------------------------------
| PAYMENT ROUTE
|--------------------------------------------------------------------------
*/

Route::get('/admin/bookings/fullpay/{id}', function ($id, SupabaseService $supabase) {

    $supabase->updateBooking($id, [
        'payment_status' => 'fully_paid'
    ]);

    return back()->with('success', 'Fully paid');
});
/*
|--------------------------------------------------------------------------
| CHECKED-IN MODULE used for checking out
|--------------------------------------------------------------------------
*/
Route::get('/admin/bookings/checked-in', function (App\Services\SupabaseService $supabase) {

    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }

    $bookings = collect($supabase->getBookings());
    $rooms = collect($supabase->getRooms());

    $bookings = $bookings->map(function ($b) use ($rooms) {

        $room = $rooms->firstWhere('uuid_id', $b['room_uuid']);

        $b['room_number'] = $room['room_number'] ?? 'N/A';
        $b['room_type'] = $room['name'] ?? 'N/A';

        return $b;
    })->where('status', 'checked_in')->values();

    return view('admin.bookings-checked-in', compact('bookings'));
});
/*
|--------------------------------------------------------------------------
| CHECK-OUT MODULE
|--------------------------------------------------------------------------
*/
Route::get('/admin/bookings/checkout/{id}', function ($id, App\Services\SupabaseService $supabase) {

    $booking = collect($supabase->getBookings())
        ->firstWhere('id', $id);

    if (!$booking) {
        return back()->with('error', 'Booking not found');
    }

    // 🟢 STEP 1 — UPDATE BOOKING STATUS
    $supabase->updateBooking($id, [
        'status' => 'checked_out'  
    ]);
    $supabase->syncRoomStatus($booking['room_uuid'], 'available');

    // 🟢 STEP 2 — FREE THE ROOM
    $supabase->updateRoom($booking['room_uuid'], [
        'status' => 'available'
    ]);

    return back()->with('success', 'Guest checked out successfully');
});

/*
|--------------------------------------------------------------------------
| DOWNPAYMENT ROUTE
|--------------------------------------------------------------------------
*/
Route::get('/admin/bookings/downpayment/{id}', function ($id, SupabaseService $supabase) {

    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }

    $booking = collect($supabase->getBookings())
        ->firstWhere('id', $id);

    if (!$booking) {
        return back()->with('error', 'Booking not found');
    }

    if (($booking['payment_status'] ?? 'unpaid') !== 'unpaid') {
        return back()->with('error', 'Already processed');
    }

    // 🟢 AUTO CONFIRM SYSTEM (OPTION B)
    $supabase->updateBooking($id, [
        'payment_status' => 'downpayment_paid',
        'status' => 'confirmed'
    ]);

    return back()->with('success', '✅ Downpayment received + AUTO CONFIRMED');
});


/*
|--------------------------------------------------------------------------
| ADMIN DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/admin/dashboard', function (App\Services\SupabaseService $supabase) {

    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }

    
    $rooms = collect($supabase->getRooms());
    $bookings = collect($supabase->getBookings());

    
//TodayCheckIns
    $today = date('Y-m-d');
$todayCheckins = $bookings->filter(function ($b) use ($today) {
    return $b['check_in'] === $today
        && in_array($b['status'], ['reserved', 'checked_in']);
});

//TodayCheckOuts
    $todayCheckouts = $bookings->filter(function ($b) use ($today) {
    return $b['check_out'] === $today
        && $b['status'] === 'checked_in';
});

    // ROOM STATS
    $totalRooms = $rooms->count();

    // occupied based on actual check-in
    $today = date('Y-m-d');

$occupiedRooms = $bookings->filter(function ($b) use ($today) {
    return $b['status'] === 'checked_in'
        && $b['check_in'] <= $today
        && $b['check_out'] >= $today;
});

   

    $availableRooms = $totalRooms - $occupiedRooms;

    // BOOKING STATS
    $pendingBookings = $bookings->where('status', 'pending')->count();
    $confirmedBookings = $bookings->where('status', 'confirmed')->count();

    return view('admin.dashboard', compact(
    'totalRooms',
    'availableRooms',
    'occupiedRooms',
    'pendingBookings',
    'confirmedBookings',
    'todayCheckins',
    'todayCheckouts'
));
});


/*
|--------------------------------------------------------------------------
| CHECK-IN ACTION (ADMIN)
|--------------------------------------------------------------------------
*/
Route::post('/admin/check-in/{id}', function (
    $id,
    SupabaseService $supabase
) {

    $booking = $supabase->getBooking($id);

    // 🔥 UPDATE BOOKING
    $supabase->updateBooking($id, [
    'status' => 'checked_in'
]);

$supabase->updateRoom($booking['room_uuid'], [
    'status' => 'occupied'
]);

    return back();
});

/*
|--------------------------------------------------------------------------
| CHECK-OUT ACTION (ADMIN)
|--------------------------------------------------------------------------
*/
Route::post('/admin/check-out/{id}', function (
    $id,
    SupabaseService $supabase
) {

    $booking = $supabase->getBooking($id);

    $supabase->updateBooking($id, [
    'status' => 'checked_out'
]);

$supabase->updateRoom($booking['room_uuid'], [
    'status' => 'available'
]);

    return back();
});
/*
|--------------------------------------------------------------------------
| POST ROUTE (CREATE BOOKING)
|--------------------------------------------------------------------------
*/
Route::post('/admin/bookings/{id}/downpayment', function (
    $id,
    SupabaseService $supabase
) {

    $supabase->updateBooking($id, [
        'payment_status' => 'downpayment_paid',
        'status' => 'confirmed'
    ]);

    $supabase->syncRoomStatus($room['uuid_id']);

    return back()->with('success', 'Downpayment saved');
});

/*
|--------------------------------------------------------------------------
| CONFIRM BOOKING
|--------------------------------------------------------------------------
*/
Route::post('/admin/bookings/{id}/confirm', function ($id, SupabaseService $supabase) {

    $supabase->updateBooking($id, [
    'status' => 'confirmed'
]);

$supabase->updateRoom($booking['room_uuid'], [
    'status' => 'reserved'
]);

    $supabase->syncRoomStatus($booking['room_uuid']);

    return back()->with('success', 'Booking confirmed');
});

/*
|--------------------------------------------------------------------------
| CHECK-IN BOOKING
|--------------------------------------------------------------------------
*/
Route::post('/admin/bookings/{id}/checkin', function (
    $id,
    SupabaseService $supabase
) {

    $booking = collect($supabase->getBookings())
        ->firstWhere('id', $id);

    if (!$booking) {
        return back()->with('error', 'Booking not found');
    }

    if (($booking['payment_status'] ?? '') !== 'fully_paid') {
        return back()->with('error', 'Must be fully paid');
    }

    $supabase->updateBooking($id, [
        'status' => 'checked_in'
    ]);

    $supabase->updateRoom($booking['room_uuid'], [
        'status' => 'occupied'
    ]);

    $supabase->syncRoomStatus($booking['room_uuid']);

    return back()->with('success', 'Checked in');
});

/*
|--------------------------------------------------------------------------
| CHECK-OUT BOOKING
|--------------------------------------------------------------------------
*/
Route::post('/admin/bookings/{id}/checkout', function (
    $id,
    SupabaseService $supabase
) {

    $booking = collect($supabase->getBookings())
        ->firstWhere('id', $id);

    $supabase->updateBooking($id, [
        'status' => 'checked_out'
    ]);

    $supabase->updateRoom($booking['room_uuid'], [
        'status' => 'available'
    ]);

    $supabase->syncRoomStatus($booking['room_uuid']);

    return back()->with('success', 'Checked out');
});

Route::get('/book-category/{categoryId}', function (
    $categoryId,
    SupabaseService $supabase
) {

    $categories = collect($supabase->getRoomCategories());
    $rooms = collect($supabase->getRooms());

    // 🔥 GET CATEGORY (SOURCE OF TRUTH)
    $category = $categories->firstWhere('id', $categoryId);

    if (!$category) {
        abort(404);
    }

    // 🔥 FORCE PRICE FROM CATEGORY (NO FALLBACK 0)
    if (!isset($category['price']) || $category['price'] <= 0) {
        return back()->with('error', 'Category price not set');
    }

    $price = (float) $category['price'];

    // pick room under category
    $room = $rooms->firstWhere('category_id', $categoryId);

    if (!$room) {
        return back()->with('error', 'No room found');
    }

    $room['price'] = $price;
    $room['category_name'] = $category['name'];

    $room['downpayment'] = $price * 0.5;
    $room['balance'] = $price * 0.5;

    return view('book-category', compact('room'));
});


/*
|--------------------------------------------------------------------------
| ADMIN ROOMS CATEGORY
|--------------------------------------------------------------------------
*/


Route::get('/admin/categories', function (SupabaseService $supabase) {

    $categories = collect($supabase->getRoomCategories());

    return view('admin.categories', compact('categories'));
});

Route::post('/admin/categories/create', function (Request $request, SupabaseService $supabase) {

    $supabase->createRoomCategory([
        'name' => $request->name,
        'price' => (float) $request->price,
        'description' => $request->description,
    ]);

    return back()->with('success', 'Category created');
});


Route::post('/admin/categories/store', function (Request $request, SupabaseService $supabase) {
 
    $supabase->createRoomCategory([
        'name' => $request->name,
        'price' => $request->price
    ]);

    return back()->with('success', 'Category created successfully');
});

Route::get('/admin/categories/delete/{id}', function ($id, SupabaseService $supabase) {

    // safety check: prevent delete if used by rooms
    $rooms = collect($supabase->getRooms());

    $used = $rooms->contains('category_id', $id);

    if ($used) {
        return back()->with('error', 'Cannot delete: category used by rooms');
    }

    $supabase->deleteRoomCategory($id);

    return back()->with('success', 'Category deleted');
});

Route::get('/admin/categories/edit/{id}', function ($id, SupabaseService $supabase) {

    $categories = collect($supabase->getRoomCategories());

    $category = $categories->firstWhere('id', $id);

    if (!$category) {
        return back()->with('error', 'Category not found');
    }

    return view('admin.categories-edit', compact('category'));
});

Route::post('/admin/categories/update/{id}', function ($id, Illuminate\Http\Request $request, SupabaseService $supabase) {

    $response = $supabase->updateRoomCategory($id, [
        'name' => $request->name,
        'price' => $request->price,
        'description' => $request->description,
    ]);

    if (!$response) {
        return back()->with('error', 'Update failed');
    }

    return redirect('/admin/categories')
        ->with('success', 'Category updated successfully');
});

/*
|--------------------------------------------------------------------------
| PAYMENT ROUTE (#)
|--------------------------------------------------------------------------
*/
Route::get('/admin/bookings/payment/{id}', function ($id, App\Services\SupabaseService $supabase) {

    $booking = collect($supabase->getBookings())
        ->firstWhere('id', $id);

    if (!$booking) {
        return back()->with('error', 'Booking not found');
    }

    return view('admin.payment', compact('booking'));
});
/*
|--------------------------------------------------------------------------
| PAYMENT LOGIC ROUTE (#)
|--------------------------------------------------------------------------
*/
Route::post('/admin/bookings/payment/{id}', function ($id, Illuminate\Http\Request $request, App\Services\SupabaseService $supabase) {

    $booking = collect($supabase->getBookings())
        ->firstWhere('id', $id);

    if (!$booking) {
        return back()->with('error', 'Booking not found');
    }

    $cash = (float) $request->cash_received;
    $total = $booking['total_amount'];
    $alreadyPaid = $booking['paid_amount'] ?? 0;
    $balance = $total - $alreadyPaid;

    $cashReceived = (float) $request->cash_received;

    $newPaid = $alreadyPaid + $cashReceived;
    $newBalance = $total - $newPaid;

    if ($newBalance <= 0) {
        $payment_status = 'paid';
        $newBalance = 0;
    } else {
        $payment_status = 'partial';
    }

    if ($cash <= 0) {
        return back()->with('error', 'Invalid cash');
    }

    // 💳 FULL PAYMENT
    if ($request->payment_type === 'full') {

        if ($cashReceived < $balance) {
    return back()->with('error', 'Insufficient cash. Remaining balance is ' . $balance);
}

        $supabase->updateBooking($id, [
            'paid_amount' => $total,
            'balance_amount' => 0,
            'payment_status' => 'paid',
            'status' => 'confirmed'
        ]);
    }

    // 💰 PARTIAL PAYMENT
    if ($request->payment_type === 'partial') {

        $balance = $total - $cash;

        $supabase->updateBooking($id, [
            'paid_amount' => $cash,
            'balance_amount' => $balance,
            'payment_status' => 'partial',
            'status' => 'confirmed'
        ]);
    }

    return redirect('/admin/bookings')
        ->with('success', 'Payment updated successfully');
});