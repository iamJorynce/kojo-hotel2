<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\RoomController;
use App\Services\SupabaseService;


/*
|--------------------------------------------------------------------------
| AUTH MIDDLEWARE
|--------------------------------------------------------------------------
*/
function adminGuard() {
    if (!session('admin_logged_in')) {
        return redirect('/admin/login');
    }
    return null;
}

/*
|==========================================================================
| PUBLIC PAGES
|==========================================================================
*/

Route::get('/', function (SupabaseService $supabase) {
    $rooms      = collect($supabase->getRooms())->groupBy('category_id');
    $categories = $supabase->getRoomCategories();
    return view('home', compact('rooms', 'categories'));
});

Route::get('/rooms', function (Request $request, SupabaseService $supabase) {
    $rooms      = collect($supabase->getRooms());
    $categories = collect($supabase->getRoomCategories());
    $checkIn    = $request->check_in;
    $checkOut   = $request->check_out;

    $rooms = $rooms->map(function ($room) use ($supabase, $checkIn, $checkOut, $categories) {
        $available   = true;
        $bookedUntil = null;

        if ($checkIn && $checkOut) {
            $result      = $supabase->checkAvailability($room['uuid_id'], $checkIn, $checkOut);
            $available   = $result['available'] ?? true;
            $bookedUntil = $result['booked_until'] ?? null;
        }

        $category              = $categories->firstWhere('id', $room['category_id']);
        $room['price']         = $category['price'] ?? 0;
        $room['category_name'] = $category['name']  ?? 'N/A';
        $room['available']     = $available;
        $room['booked_until']  = $bookedUntil;
        return $room;
    });

    return view('rooms', compact('rooms', 'categories', 'checkIn', 'checkOut'));
});

Route::get('/book-category/{categoryId}', function ($categoryId, SupabaseService $supabase) {
    $categories = collect($supabase->getRoomCategories());
    $rooms      = collect($supabase->getRooms());
    $category   = $categories->firstWhere('id', $categoryId);

    if (!$category) abort(404);
    if (!isset($category['price']) || $category['price'] <= 0) {
        return back()->with('error', 'Category price not set');
    }

    $price         = (float) $category['price'];
    $room          = $rooms->firstWhere('category_id', $categoryId);
    if (!$room) return back()->with('error', 'No room found');

    $room['price']         = $price;
    $room['category_name'] = $category['name'];
    $room['downpayment']   = $price * 0.5;
    $room['balance']       = $price * 0.5;

    return view('book-category', compact('room'));
});

Route::post('/book/{uuid}', function ($uuid, Request $request, SupabaseService $supabase) {
    $room = collect($supabase->getRooms())->firstWhere('uuid_id', $uuid);
    if (!$room) return back()->with('error', 'Room not found');

    if ($request->check_in < date('Y-m-d')) return back()->with('error', 'Invalid check-in date');
    if ($request->check_out <= $request->check_in) return back()->with('error', 'Invalid date range');

    if (!$supabase->isRoomAvailable($room['uuid_id'], $request->check_in, $request->check_out)) {
        return back()->with('error', 'Room already booked');
    }

    $category      = collect($supabase->getRoomCategories())->firstWhere('id', $room['category_id']);
    $pricePerNight = (float) ($category['price'] ?? 0);
    $checkIn       = new DateTime($request->check_in);
    $checkOut      = new DateTime($request->check_out);
    $nights        = $checkIn->diff($checkOut)->days;
    $total         = $pricePerNight * $nights;

    $response = $supabase->createBooking([
        'room_uuid'      => $room['uuid_id'],
        'room_name'      => $room['name'],
        'room_number'    => $room['room_number'] ?? null,
        'room_price'     => $pricePerNight,
        'total_amount'   => $total,
        'paid_amount'    => 0,
        'balance_amount' => $total,
        'full_name'      => $request->full_name,
        'phone'          => $request->phone,
        'email'          => $request->email,
        'check_in'       => $request->check_in,
        'check_out'      => $request->check_out,
        'nights'         => $nights,
        'status'         => 'pending',
        'payment_status' => 'unpaid',
    ]);

    if (!$response) return back()->with('error', 'Booking failed');
    return redirect('/booking-success')->with('success', 'Booking successful');
});

Route::get('/booking-success', fn() => view('booking-success'));

/*
|==========================================================================
| DAY TOUR — PUBLIC
|==========================================================================
*/

Route::get('/day-tour', function (SupabaseService $supabase) {
    $packages = $supabase->getDayTourPackages();
    return view('day-tour', compact('packages'));
});

Route::get('/day-tour/book/{packageId}', function ($packageId, SupabaseService $supabase) {
    $package = collect($supabase->getDayTourPackages())->firstWhere('id', $packageId);
    if (!$package) abort(404);
    return view('day-tour-book', compact('package'));
});

Route::post('/day-tour/book/{packageId}', function ($packageId, Request $request, SupabaseService $supabase) {
    $package = collect($supabase->getDayTourPackages())->firstWhere('id', $packageId);
    if (!$package) return back()->with('error', 'Package not found.');

    $guests = (int) $request->guest_count;
    if ($guests <= 0) return back()->with('error', 'Invalid number of guests.');
    if ($request->visit_date < date('Y-m-d')) return back()->with('error', 'Visit date cannot be in the past.');

    $pricePerPerson = (float) $package['price_per_person'];
    $total          = $pricePerPerson * $guests;

    $response = $supabase->createDayTour([
        'package_id'       => $package['id'],
        'package_name'     => $package['name'],
        'price_per_person' => $pricePerPerson,
        'guest_count'      => $guests,
        'total_amount'     => $total,
        'paid_amount'      => 0,
        'balance_amount'   => $total,
        'full_name'        => $request->full_name,
        'phone'            => $request->phone,
        'email'            => $request->email ?? null,
        'visit_date'       => $request->visit_date,
        'notes'            => $request->notes ?? null,
        'status'           => 'pending',
        'payment_status'   => 'unpaid',
        'type'             => 'advance',
    ]);

    if (!$response) return back()->with('error', 'Booking failed. Please try again.');
    return redirect('/day-tour/success')->with('success', 'Day tour booked successfully!');
});

Route::get('/day-tour/success', fn() => view('day-tour-success'));

/*
|==========================================================================
| ADMIN — LOGIN / LOGOUT
|==========================================================================
*/

Route::get('/admin/login', fn() => view('admin.login'));

Route::post('/admin/login', function (Request $request, SupabaseService $supabase) {
    $user = $supabase->loginStaff($request->email, $request->password);

    if (!$user) return back()->with('error', 'Invalid credentials or account disabled.');

    session([
        'admin_logged_in' => true,
        'admin_id'        => $user['id'],
        'admin_name'      => $user['full_name'],
        'admin_role'      => $user['role'],
    ]);

    $supabase->log('login', [
        'target_type'  => 'staff',
        'target_id'    => $user['id'],
        'target_label' => $user['full_name'] . ' (' . $user['role'] . ')',
    ]);

    return redirect('/admin/dashboard');
});

Route::get('/admin/logout', function (SupabaseService $supabase) {
    if (session('admin_logged_in')) {
        $supabase->log('logout', [
            'target_type'  => 'staff',
            'target_id'    => session('admin_id'),
            'target_label' => session('admin_name'),
        ]);
    }
    session()->flush();
    session()->regenerateToken();
    return redirect('/admin/login');
});

/*
|==========================================================================
| ADMIN — DASHBOARD
|==========================================================================
*/

Route::get('/admin/dashboard', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $rooms    = collect($supabase->getRooms());
    $bookings = collect($supabase->getBookings());
    $today    = date('Y-m-d');

    $todayCheckins = $bookings->filter(fn($b) =>
        $b['check_in'] === $today && in_array($b['status'], ['confirmed', 'checked_in'])
    );

    $todayCheckouts = $bookings->filter(fn($b) =>
        $b['check_out'] === $today && $b['status'] === 'checked_in'
    );

    $totalRooms    = $rooms->count();
    $occupiedRooms = $bookings->filter(fn($b) =>
        $b['status'] === 'checked_in' &&
        $b['check_in'] <= $today &&
        $b['check_out'] >= $today
    )->count();

    $availableRooms    = $totalRooms - $occupiedRooms;
    $pendingBookings   = $bookings->where('status', 'pending')->count();
    $confirmedBookings = $bookings->where('status', 'confirmed')->count();

    return view('admin.dashboard', compact(
        'totalRooms', 'availableRooms', 'occupiedRooms',
        'pendingBookings', 'confirmedBookings',
        'todayCheckins', 'todayCheckouts'
    ));
});

/*
|==========================================================================
| ADMIN — ROOMS
|==========================================================================
*/

Route::get('/admin/rooms', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $rooms      = $supabase->getRooms();
    $categories = collect($supabase->getRoomCategories());
    return view('admin.rooms', compact('rooms', 'categories'));
});

Route::get('/admin/rooms/create', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $rooms      = collect($supabase->getRooms());
    $categories = collect($supabase->getRoomCategories());
    return view('admin.rooms-create', compact('rooms', 'categories'));
});

Route::post('/admin/rooms/create', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $categories = collect($supabase->getRoomCategories());
    $category   = $categories->firstWhere('id', $request->category_id);
    $roomName   = $category['name'] ?? 'Room';

    $response = $supabase->createRoom([
        'name'        => $roomName,
        'room_number' => trim($request->room_number),
        'category_id' => $request->category_id,
        'status'      => 'available',
        'image_url'   => $request->image_url ?? null,
    ]);

    if (!$response) return back()->with('error', 'Failed to create room');

    $supabase->log('room_created', [
        'target_type'  => 'room',
        'target_label' => $roomName . ' Room ' . $request->room_number,
    ]);

    return back()->with('success', 'Room created successfully');
});

// Calendar routes BEFORE wildcard {id} routes
Route::get('/admin/rooms/calendar/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $room = collect($supabase->getRooms())->firstWhere('id', $id);
    if (!$room) abort(404);
    return view('admin.room-calendar', compact('room'));
});

Route::get('/admin/rooms/{id}/calendar-data', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $room = collect($supabase->getRooms())->firstWhere('id', $id);
    if (!$room) abort(404);

    $events = [];
    foreach ($supabase->getBookings() as $b) {
        if (($b['room_uuid'] ?? null) !== $room['uuid_id']) continue;
        $events[] = [
            'title' => ($b['full_name'] ?? 'Guest') . ' - Room ' . ($b['room_number'] ?? ''),
            'start' => $b['check_in'],
            'end'   => date('Y-m-d', strtotime($b['check_out'] . ' +1 day')),
            'color' => match($b['status'] ?? 'pending') {
                'confirmed'  => 'green',
                'pending'    => 'orange',
                'checked_in' => 'blue',
                'cancelled'  => 'red',
                default      => 'gray',
            },
        ];
    }
    return response()->json($events);
});

Route::get('/admin/rooms/edit/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $rooms      = collect($supabase->getRooms());
    $categories = collect($supabase->getRoomCategories());
    $room       = $rooms->firstWhere('id', $id);
    if (!$room) return back()->with('error', 'Room not found');
    return view('admin.rooms-edit', compact('room', 'categories'));
});

Route::post('/admin/rooms/update/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $supabase->updateRoom($id, [
        'room_number' => $request->room_number,
        'category_id' => $request->category_id,
        'status'      => $request->status,
    ]);
    return redirect('/admin/rooms')->with('success', 'Room updated successfully');
});

Route::get('/admin/rooms/delete/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $response = $supabase->deleteRoom($id);
    if (!$response) return back()->with('error', 'Failed to delete room');

    $supabase->log('room_deleted', [
        'target_type' => 'room',
        'target_id'   => $id,
    ]);

    return back()->with('success', 'Room deleted successfully');
});

/*
|==========================================================================
| ADMIN — BOOKINGS
|==========================================================================
*/

Route::get('/admin/bookings', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $status   = $request->get('status', 'all');
    $bookings = collect($supabase->getBookings());

    $bookings = $bookings->map(function ($b) use ($bookings) {
        $b['has_conflict'] = $bookings->contains(function ($x) use ($b) {
            if ($x['id'] === $b['id']) return false;
            if (($x['room_uuid'] ?? null) !== ($b['room_uuid'] ?? null)) return false;
            if (!in_array($x['status'], ['confirmed', 'checked_in'])) return false;
            return $x['check_in'] < $b['check_out'] && $x['check_out'] > $b['check_in'];
        });
        return $b;
    });

    if ($status !== 'all') $bookings = $bookings->where('status', $status);

    return view('admin.bookings', compact('bookings', 'status'));
});

Route::get('/admin/bookings/confirmed', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    return app(RoomController::class)->confirmedBookings();
});

Route::get('/admin/bookings/create', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $rooms      = $supabase->getRooms();
    $categories = $supabase->getRoomCategories();
    return view('admin.bookings-create', compact('rooms', 'categories'));
});

Route::post('/admin/bookings/create', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $room = collect($supabase->getRooms())->firstWhere('uuid_id', $request->room_id);
    if (!$room) return back()->with('error', 'Room not found');

    $category      = collect($supabase->getRoomCategories())->firstWhere('id', $room['category_id']);
    $pricePerNight = (float) ($category['price'] ?? 0);
    if ($pricePerNight <= 0) return back()->with('error', 'Invalid room price');

    $checkIn  = new DateTime($request->check_in);
    $checkOut = new DateTime($request->check_out);
    $nights   = $checkIn->diff($checkOut)->days;
    if ($nights <= 0) return back()->with('error', 'Invalid number of nights');

    $total        = $pricePerNight * $nights;
    $availability = $supabase->checkAvailability($room['uuid_id'], $request->check_in, $request->check_out);
    if (!($availability['available'] ?? false)) return back()->with('error', 'Room already booked');

    $cashReceived = (float) $request->cash_received;
    if ($cashReceived >= $total) {
        $payment_status = 'paid';   $paid_amount = $total;  $balance = 0;
    } elseif ($cashReceived > 0) {
        $payment_status = 'partial'; $paid_amount = $cashReceived; $balance = $total - $cashReceived;
    } else {
        $payment_status = 'unpaid';  $paid_amount = 0; $balance = $total;
    }

    $response = $supabase->createBooking([
        'room_uuid'        => $room['uuid_id'],
        'room_name'        => $room['name']        ?? '',
        'room_number'      => $room['room_number']  ?? '',
        'room_price'       => $pricePerNight,
        'room_description' => $room['description'] ?? '',
        'full_name'        => $request->full_name,
        'phone'            => $request->phone,
        'email'            => $request->email ?? null,
        'check_in'         => $request->check_in,
        'check_out'        => $request->check_out,
        'nights'           => $nights,
        'status'           => 'confirmed',
        'payment_status'   => $payment_status,
        'total_amount'     => $total,
        'paid_amount'      => $paid_amount,
        'balance_amount'   => $balance,
    ]);

    if (!$response) return back()->with('error', 'Booking failed');

    $supabase->updateRoom($room['uuid_id'], ['status' => 'reserved']);

    $supabase->log('booking_created', [
        'target_type'  => 'booking',
        'target_label' => $request->full_name . ' — ' . ($room['name'] ?? '') . ' Room ' . ($room['room_number'] ?? '') . ' (' . $nights . ' nights)',
        'amount'       => $paid_amount,
        'payment_type' => $payment_status !== 'unpaid' ? $payment_status : null,
    ]);

    if ($paid_amount > 0) {
        $supabase->recordPayment([
            'target_type'     => 'booking',
            'target_id'       => $response[0]['id'] ?? '',
            'guest_name'      => $request->full_name,
            'room_info'       => ($room['name'] ?? '') . ' Room ' . ($room['room_number'] ?? ''),
            'amount_received' => $paid_amount,
            'payment_type'    => $payment_status,
            'payment_method'  => 'cash',
            'total_amount'    => $total,
            'balance_after'   => $balance,
        ]);
    }

    return redirect('/admin/bookings')->with('success', 'Booking created successfully');
});

Route::get('/admin/bookings/confirm/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $bookings = collect($supabase->getBookings());
    $booking  = $bookings->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');

    $conflict = $bookings->contains(function ($b) use ($booking) {
        if ($b['id'] === $booking['id']) return false;
        if (($b['room_uuid'] ?? null) !== ($booking['room_uuid'] ?? null)) return false;
        if (!in_array($b['status'], ['confirmed', 'checked_in'])) return false;
        return $b['check_in'] < $booking['check_out'] && $b['check_out'] > $booking['check_in'];
    });

    if ($conflict) return back()->with('error', 'Room already reserved for these dates.');

    $supabase->updateBooking($id, ['status' => 'confirmed']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'reserved');

    $supabase->log('booking_confirmed', [
        'target_type'  => 'booking',
        'target_id'    => $id,
        'target_label' => ($booking['full_name'] ?? '') . ' — ' . ($booking['room_name'] ?? ''),
    ]);

    return back()->with('success', 'Booking confirmed');
});

Route::get('/admin/bookings/cancel/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $booking = $supabase->getBookingById($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    if ($booking['status'] === 'checked_in') return back()->with('error', 'Cannot cancel a checked-in booking');
    if ($booking['payment_status'] === 'paid') return back()->with('error', 'Cannot cancel a paid booking');

    $supabase->updateBooking($id, ['status' => 'cancelled']);

    $supabase->log('booking_cancelled', [
        'target_type'  => 'booking',
        'target_id'    => $id,
        'target_label' => ($booking['full_name'] ?? '') . ' — ' . ($booking['room_name'] ?? ''),
    ]);

    return back()->with('success', 'Booking cancelled');
});

Route::get('/admin/bookings/checkin/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $booking = collect($supabase->getBookings())->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');

    if (($booking['payment_status'] ?? '') !== 'paid' || ($booking['balance_amount'] ?? 1) > 0) {
        return back()->with('error', 'Full payment required before check-in');
    }

    $supabase->updateBooking($id, ['status' => 'checked_in']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'occupied');

    $supabase->log('checkin', [
        'target_type'  => 'booking',
        'target_id'    => $id,
        'target_label' => ($booking['full_name'] ?? '') . ' — ' . ($booking['room_name'] ?? '') . ' Room ' . ($booking['room_number'] ?? ''),
    ]);

    return back()->with('success', 'Guest checked in');
});

Route::get('/admin/bookings/fullpay/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $booking = collect($supabase->getBookings())->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');

    $total = (float) ($booking['total_amount'] ?? 0);

    $supabase->updateBooking($id, [
        'payment_status' => 'paid',
        'paid_amount'    => $total,
        'balance_amount' => 0,
    ]);

    $supabase->log('payment_received', [
        'target_type'  => 'booking',
        'target_id'    => $id,
        'target_label' => ($booking['full_name'] ?? '') . ' — Full Payment',
        'amount'       => $total,
        'payment_type' => 'full',
    ]);

    $supabase->recordPayment([
        'target_type'     => 'booking',
        'target_id'       => $id,
        'guest_name'      => $booking['full_name']  ?? '',
        'room_info'       => ($booking['room_name'] ?? '') . ' Room ' . ($booking['room_number'] ?? ''),
        'amount_received' => $total,
        'payment_type'    => 'full',
        'payment_method'  => 'cash',
        'total_amount'    => $total,
        'balance_after'   => 0,
    ]);

    return back()->with('success', 'Fully paid');
});

Route::get('/admin/bookings/downpayment/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $booking = collect($supabase->getBookings())->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');
    if (($booking['payment_status'] ?? 'unpaid') !== 'unpaid') return back()->with('error', 'Already processed');

    $supabase->updateBooking($id, ['payment_status' => 'partial', 'status' => 'confirmed']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'reserved');

    return back()->with('success', 'Downpayment received + confirmed');
});

Route::get('/admin/bookings/payment/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $booking = collect($supabase->getBookings())->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');
    return view('admin.payment', compact('booking'));
});

Route::post('/admin/bookings/payment/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;

    $booking = collect($supabase->getBookings())->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');

    $cash = (float) $request->cash_received;
    if ($cash <= 0) return back()->with('error', 'Invalid cash amount');

    $total       = (float) $booking['total_amount'];
    $alreadyPaid = (float) ($booking['paid_amount'] ?? 0);
    $balance     = $total - $alreadyPaid;
    $newBalance  = 0;

    if ($request->payment_type === 'full') {
        if ($cash < $balance) return back()->with('error', 'Insufficient cash. Balance is ₱' . number_format($balance, 2));
        $supabase->updateBooking($id, [
            'paid_amount' => $total, 'balance_amount' => 0,
            'payment_status' => 'paid', 'status' => 'confirmed',
        ]);
        $newBalance = 0;
    }

    if ($request->payment_type === 'partial') {
        $newPaid    = $alreadyPaid + $cash;
        $newBalance = max(0, $total - $newPaid);
        $supabase->updateBooking($id, [
            'paid_amount'    => $newPaid,
            'balance_amount' => $newBalance,
            'payment_status' => $newBalance <= 0 ? 'paid' : 'partial',
            'status'         => 'confirmed',
        ]);
    }

    $supabase->log('payment_received', [
        'target_type'  => 'booking',
        'target_id'    => $id,
        'target_label' => ($booking['full_name'] ?? '') . ' — ' . ($booking['room_name'] ?? '') . ' Room ' . ($booking['room_number'] ?? ''),
        'amount'       => $cash,
        'payment_type' => $request->payment_type,
    ]);

    $supabase->recordPayment([
        'target_type'     => 'booking',
        'target_id'       => $id,
        'guest_name'      => $booking['full_name']  ?? '',
        'room_info'       => ($booking['room_name'] ?? '') . ' Room ' . ($booking['room_number'] ?? ''),
        'amount_received' => $cash,
        'payment_type'    => $request->payment_type,
        'payment_method'  => 'cash',
        'total_amount'    => $total,
        'balance_after'   => $newBalance,
    ]);

    return redirect('/admin/bookings')->with('success', 'Payment updated successfully');
});

Route::get('/admin/bookings/checked-in', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $bookings = collect($supabase->getBookings());
    $rooms    = collect($supabase->getRooms());
    $bookings = $bookings->map(function ($b) use ($rooms) {
        $room             = $rooms->firstWhere('uuid_id', $b['room_uuid']);
        $b['room_number'] = $room['room_number'] ?? 'N/A';
        $b['room_type']   = $room['name']        ?? 'N/A';
        return $b;
    })->where('status', 'checked_in')->values();
    return view('admin.bookings-checked-in', compact('bookings'));
});

Route::get('/admin/bookings/checkout/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $booking = collect($supabase->getBookings())->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');

    $supabase->updateBooking($id, ['status' => 'checked_out']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'available');

    $supabase->log('checkout', [
        'target_type'  => 'booking',
        'target_id'    => $id,
        'target_label' => ($booking['full_name'] ?? '') . ' — ' . ($booking['room_name'] ?? '') . ' Room ' . ($booking['room_number'] ?? ''),
    ]);

    return back()->with('success', 'Guest checked out successfully');
});

Route::get('/admin/booking-calendar', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $bookings = $supabase->getBookings();
    $rooms    = collect($supabase->getRooms());
    $events   = [];
    foreach ($bookings as $b) {
        $room     = $rooms->firstWhere('uuid_id', $b['room_uuid'] ?? null);
        $events[] = [
            'title'  => ($b['full_name'] ?? 'Guest') . ' - ' . ($b['room_name'] ?? '') . ' - Room ' . ($b['room_number'] ?? ''),
            'start'  => $b['check_in'],
            'end'    => date('Y-m-d', strtotime($b['check_out'] . ' +1 day')),
            'color'  => match($b['status'] ?? 'pending') {
                'pending' => 'orange', 'confirmed' => 'green',
                'checked_in' => 'blue', 'cancelled' => 'red', default => 'gray',
            },
            'extendedProps' => [
                'guest'       => $b['full_name']      ?? '-',
                'phone'       => $b['phone']          ?? '-',
                'room_number' => $room['room_number'] ?? '-',
                'room_type'   => $room['name']        ?? '-',
                'check_in'    => $b['check_in']       ?? '-',
                'check_out'   => $b['check_out']      ?? '-',
                'status'      => $b['status']         ?? '-',
            ],
        ];
    }
    return view('admin.booking-calendar', compact('events'));
});

// POST versions for form-based actions
Route::post('/admin/check-in/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $booking = $supabase->getBookingById($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    $supabase->updateBooking($id, ['status' => 'checked_in']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'occupied');
    $supabase->log('checkin', ['target_type' => 'booking', 'target_id' => $id, 'target_label' => $booking['full_name'] ?? '']);
    return back()->with('success', 'Checked in');
});

Route::post('/admin/check-out/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $booking = $supabase->getBookingById($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    $supabase->updateBooking($id, ['status' => 'checked_out']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'available');
    $supabase->log('checkout', ['target_type' => 'booking', 'target_id' => $id, 'target_label' => $booking['full_name'] ?? '']);
    return back()->with('success', 'Checked out');
});

Route::post('/admin/bookings/{id}/confirm', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $bookings = collect($supabase->getBookings());
    $booking  = $bookings->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');
    $supabase->updateBooking($id, ['status' => 'confirmed']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'reserved');
    $supabase->log('booking_confirmed', ['target_type' => 'booking', 'target_id' => $id, 'target_label' => $booking['full_name'] ?? '']);
    return back()->with('success', 'Booking confirmed');
});

Route::post('/admin/bookings/{id}/checkin', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $booking = collect($supabase->getBookings())->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');
    if (($booking['payment_status'] ?? '') !== 'paid') return back()->with('error', 'Must be fully paid');
    $supabase->updateBooking($id, ['status' => 'checked_in']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'occupied');
    $supabase->log('checkin', ['target_type' => 'booking', 'target_id' => $id, 'target_label' => $booking['full_name'] ?? '']);
    return back()->with('success', 'Checked in');
});

Route::post('/admin/bookings/{id}/checkout', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $booking = collect($supabase->getBookings())->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');
    $supabase->updateBooking($id, ['status' => 'checked_out']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'available');
    $supabase->log('checkout', ['target_type' => 'booking', 'target_id' => $id, 'target_label' => $booking['full_name'] ?? '']);
    return back()->with('success', 'Checked out');
});

Route::post('/admin/bookings/{id}/downpayment', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $booking = collect($supabase->getBookings())->firstWhere('id', $id);
    if (!$booking) return back()->with('error', 'Booking not found');
    $supabase->updateBooking($id, ['payment_status' => 'partial', 'status' => 'confirmed']);
    $supabase->syncRoomStatus($booking['room_uuid'], 'reserved');
    return back()->with('success', 'Downpayment saved');
});

/*
|==========================================================================
| ADMIN — CATEGORIES
|==========================================================================
*/

Route::get('/admin/categories', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $categories = collect($supabase->getRoomCategories());
    return view('admin.categories', compact('categories'));
});

Route::post('/admin/categories/create', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $supabase->createRoomCategory([
        'name' => $request->name, 'price' => (float) $request->price, 'description' => $request->description ?? '',
    ]);
    return back()->with('success', 'Category created');
});

Route::get('/admin/categories/delete/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $used = collect($supabase->getRooms())->contains('category_id', $id);
    if ($used) return back()->with('error', 'Cannot delete: category is used by rooms');
    $supabase->deleteRoomCategory($id);
    return back()->with('success', 'Category deleted');
});

Route::get('/admin/categories/edit/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $category = collect($supabase->getRoomCategories())->firstWhere('id', $id);
    if (!$category) return back()->with('error', 'Category not found');
    return view('admin.categories-edit', compact('category'));
});

Route::post('/admin/categories/update/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $response = $supabase->updateRoomCategory($id, [
        'name' => $request->name, 'price' => $request->price, 'description' => $request->description,
    ]);
    if (!$response) return back()->with('error', 'Update failed');
    return redirect('/admin/categories')->with('success', 'Category updated successfully');
});

/*
|==========================================================================
| ADMIN — DAY TOURS
|==========================================================================
*/

Route::get('/admin/day-tours', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $status   = $request->get('status', 'all');
    $date     = $request->get('date', '');
    $dayTours = collect($supabase->getDayTours());
    if ($status !== 'all') $dayTours = $dayTours->where('status', $status);
    if ($date) $dayTours = $dayTours->where('visit_date', $date);
    $today        = date('Y-m-d');
    $todayTours   = collect($supabase->getDayTours())->where('visit_date', $today);
    $todayGuests  = $todayTours->sum('guest_count');
    $todayRevenue = $todayTours->where('payment_status', 'paid')->sum('total_amount');
    return view('admin.day-tours', compact('dayTours', 'status', 'date', 'todayGuests', 'todayRevenue'));
});

Route::get('/admin/day-tours/walkin', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $packages = $supabase->getDayTourPackages();
    return view('admin.day-tour-walkin', compact('packages'));
});

Route::post('/admin/day-tours/walkin', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $package = collect($supabase->getDayTourPackages())->firstWhere('id', $request->package_id);
    if (!$package) return back()->with('error', 'Package not found.');

    $guests         = (int) $request->guest_count;
    $pricePerPerson = (float) $package['price_per_person'];
    $total          = $pricePerPerson * $guests;
    $cash           = (float) $request->cash_received;

    if ($guests <= 0) return back()->with('error', 'Invalid guest count.');
    if ($cash < 0)    return back()->with('error', 'Invalid cash amount.');

    if ($cash >= $total)      { $paymentStatus = 'paid';    $paidAmount = $total; $balance = 0; }
    elseif ($cash > 0)        { $paymentStatus = 'partial'; $paidAmount = $cash;  $balance = $total - $cash; }
    else                      { $paymentStatus = 'unpaid';  $paidAmount = 0;      $balance = $total; }

    $response = $supabase->createDayTour([
        'package_id'       => $package['id'],
        'package_name'     => $package['name'],
        'price_per_person' => $pricePerPerson,
        'guest_count'      => $guests,
        'total_amount'     => $total,
        'paid_amount'      => $paidAmount,
        'balance_amount'   => $balance,
        'full_name'        => $request->full_name,
        'phone'            => $request->phone,
        'email'            => $request->email ?? null,
        'visit_date'       => date('Y-m-d'),
        'notes'            => $request->notes ?? null,
        'status'           => 'confirmed',
        'payment_status'   => $paymentStatus,
        'type'             => 'walk_in',
    ]);

    if (!$response) return back()->with('error', 'Failed to create day tour.');

    $supabase->log('day_tour_created', [
        'target_type'  => 'day_tour',
        'target_label' => $request->full_name . ' — ' . $package['name'] . ' (' . $guests . ' guests)',
        'amount'       => $paidAmount,
        'payment_type' => $paymentStatus !== 'unpaid' ? $paymentStatus : null,
    ]);

    if ($paidAmount > 0) {
        $supabase->recordPayment([
            'target_type'     => 'day_tour',
            'target_id'       => $response[0]['id'] ?? '',
            'guest_name'      => $request->full_name,
            'room_info'       => $package['name'] . ' — ' . $guests . ' guest(s)',
            'amount_received' => $paidAmount,
            'payment_type'    => $paymentStatus,
            'payment_method'  => 'cash',
            'total_amount'    => $total,
            'balance_after'   => $balance,
        ]);
    }

    return redirect('/admin/day-tours')->with('success', 'Walk-in day tour created!');
});

Route::get('/admin/day-tours/confirm/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $tour = $supabase->getDayTourById($id);
    if (!$tour) return back()->with('error', 'Day tour not found.');
    $supabase->updateDayTour($id, ['status' => 'confirmed']);
    $supabase->log('day_tour_created', ['target_type' => 'day_tour', 'target_id' => $id, 'target_label' => ($tour['full_name'] ?? '') . ' — confirmed']);
    return back()->with('success', 'Day tour confirmed.');
});

Route::get('/admin/day-tours/payment/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $tour = $supabase->getDayTourById($id);
    if (!$tour) return back()->with('error', 'Day tour not found.');
    return view('admin.day-tour-payment', compact('tour'));
});

Route::post('/admin/day-tours/payment/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $tour = $supabase->getDayTourById($id);
    if (!$tour) return back()->with('error', 'Day tour not found.');

    $cash        = (float) $request->cash_received;
    $total       = (float) $tour['total_amount'];
    $alreadyPaid = (float) ($tour['paid_amount'] ?? 0);
    $balance     = $total - $alreadyPaid;
    $newBalance  = 0;

    if ($cash <= 0) return back()->with('error', 'Invalid cash amount.');

    if ($request->payment_type === 'full') {
        if ($cash < $balance) return back()->with('error', 'Insufficient cash. Balance is ₱' . number_format($balance, 2));
        $supabase->updateDayTour($id, ['paid_amount' => $total, 'balance_amount' => 0, 'payment_status' => 'paid', 'status' => 'confirmed']);
        $newBalance = 0;
    } else {
        $newPaid    = $alreadyPaid + $cash;
        $newBalance = max(0, $total - $newPaid);
        $supabase->updateDayTour($id, [
            'paid_amount'    => $newPaid,
            'balance_amount' => $newBalance,
            'payment_status' => $newBalance <= 0 ? 'paid' : 'partial',
            'status'         => 'confirmed',
        ]);
    }

    $supabase->log('day_tour_payment', [
        'target_type'  => 'day_tour',
        'target_id'    => $id,
        'target_label' => ($tour['full_name'] ?? '') . ' — ' . ($tour['package_name'] ?? '') . ' (' . ($tour['guest_count'] ?? 0) . ' guests)',
        'amount'       => $cash,
        'payment_type' => $request->payment_type,
    ]);

    $supabase->recordPayment([
        'target_type'     => 'day_tour',
        'target_id'       => $id,
        'guest_name'      => $tour['full_name']    ?? '',
        'room_info'       => ($tour['package_name'] ?? '') . ' — ' . ($tour['guest_count'] ?? 0) . ' guest(s)',
        'amount_received' => $cash,
        'payment_type'    => $request->payment_type,
        'payment_method'  => 'cash',
        'total_amount'    => $total,
        'balance_after'   => $newBalance,
    ]);

    return redirect('/admin/day-tours')->with('success', 'Payment updated.');
});

Route::get('/admin/day-tours/cancel/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $tour = $supabase->getDayTourById($id);
    if (!$tour) return back()->with('error', 'Day tour not found.');
    if ($tour['payment_status'] === 'paid') return back()->with('error', 'Cannot cancel a fully paid tour.');
    $supabase->updateDayTour($id, ['status' => 'cancelled']);
    $supabase->log('booking_cancelled', ['target_type' => 'day_tour', 'target_id' => $id, 'target_label' => $tour['full_name'] ?? '']);
    return back()->with('success', 'Day tour cancelled.');
});

Route::get('/admin/day-tour-packages', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $packages = collect($supabase->getDayTourPackages());
    return view('admin.day-tour-packages', compact('packages'));
});

Route::post('/admin/day-tour-packages/create', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    if ((float)$request->price_per_person <= 0) return back()->with('error', 'Price must be greater than 0.');
    $supabase->createDayTourPackage([
        'name' => $request->name, 'description' => $request->description ?? '',
        'price_per_person' => (float) $request->price_per_person, 'inclusions' => $request->inclusions ?? '',
    ]);
    return back()->with('success', 'Package created.');
});

Route::post('/admin/day-tour-packages/update/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $supabase->updateDayTourPackage($id, [
        'name' => $request->name, 'description' => $request->description ?? '',
        'price_per_person' => (float) $request->price_per_person, 'inclusions' => $request->inclusions ?? '',
    ]);
    return back()->with('success', 'Package updated.');
});

Route::get('/admin/day-tour-packages/delete/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $supabase->deleteDayTourPackage($id);
    return back()->with('success', 'Package deleted.');
});

/*
|==========================================================================
| ADMIN — STAFF MANAGEMENT
|==========================================================================
*/

Route::get('/admin/staff', function (SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard')->with('error', 'Access denied.');
    $staff = collect($supabase->getStaff());
    return view('admin.staff', compact('staff'));
});

Route::post('/admin/staff/create', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard');
    if (strlen($request->password) < 6) return back()->with('error', 'Password must be at least 6 characters.');
    $response = $supabase->createStaff([
        'full_name' => $request->full_name, 'email' => $request->email,
        'password' => $request->password,   'role'  => $request->role,
    ]);
    if (!$response) return back()->with('error', 'Failed to create staff. Email may already exist.');
    $supabase->log('staff_created', ['target_type' => 'staff', 'target_label' => $request->full_name . ' (' . $request->role . ')']);
    return back()->with('success', 'Staff account created.');
});

Route::post('/admin/staff/update/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard');
    $data = ['full_name' => $request->full_name, 'email' => $request->email, 'role' => $request->role];
    if (!empty($request->password)) {
        if (strlen($request->password) < 6) return back()->with('error', 'Password must be at least 6 characters.');
        $data['password'] = $request->password;
    }
    $supabase->updateStaff($id, $data);
    $supabase->log('staff_updated', ['target_type' => 'staff', 'target_id' => $id, 'target_label' => $request->full_name]);
    return back()->with('success', 'Staff updated.');
});

Route::get('/admin/staff/toggle/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard');
    if ($id == session('admin_id')) return back()->with('error', 'You cannot deactivate your own account.');
    $staff = $supabase->getStaffById($id);
    if (!$staff) return back()->with('error', 'Staff not found.');
    $newStatus = !$staff['is_active'];
    $supabase->toggleStaffActive($id, $newStatus);
    $supabase->log($newStatus ? 'staff_activated' : 'staff_deactivated', ['target_type' => 'staff', 'target_id' => $id, 'target_label' => $staff['full_name']]);
    return back()->with('success', 'Staff status updated.');
});

Route::get('/admin/staff/delete/{id}', function ($id, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard');
    if ($id == session('admin_id')) return back()->with('error', 'You cannot delete your own account.');
    $staff = $supabase->getStaffById($id);
    $supabase->deleteStaff($id);
    $supabase->log('staff_deleted', ['target_type' => 'staff', 'target_id' => $id, 'target_label' => $staff['full_name'] ?? 'Unknown']);
    return back()->with('success', 'Staff deleted.');
});

/*
|==========================================================================
| ADMIN — AUDIT LOG
|==========================================================================
*/

Route::get('/admin/audit-log', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard')->with('error', 'Access denied.');
    $filters = ['staff_id' => $request->staff_id, 'action' => $request->action, 'date' => $request->date];
    $logs    = collect($supabase->getAuditLogs($filters));
    $staff   = collect($supabase->getStaff());
    return view('admin.audit-log', compact('logs', 'staff', 'filters'));
});

/*
|==========================================================================
| ADMIN — PAYMENT RECORDS
|==========================================================================
*/

Route::get('/admin/payments', function (Request $request, SupabaseService $supabase) {
    if ($r = adminGuard()) return $r;
    $filters = [
        'staff_id'    => $request->staff_id,
        'target_type' => $request->type,
        'date'        => $request->date ?? date('Y-m-d'),
    ];
    $payments        = collect($supabase->getPaymentRecords($filters));
    $staff           = collect($supabase->getStaff());
    $totalReceived   = $payments->sum('amount_received');
    $bookingPayments = $payments->where('target_type', 'booking')->sum('amount_received');
    $dayTourPayments = $payments->where('target_type', 'day_tour')->sum('amount_received');
    $perStaff        = $payments->groupBy('staff_name')->map(fn($p) => ['count' => $p->count(), 'total' => $p->sum('amount_received')]);
    return view('admin.payments', compact('payments', 'staff', 'filters', 'totalReceived', 'bookingPayments', 'dayTourPayments', 'perStaff'));
});


/*
|==========================================================================
| EQUIPMENT RENTAL ROUTES — Add to web.php
|==========================================================================
|
| Walk-in Equipment Rental:
| - /admin/equipment/walkin — form to create rental
| - /admin/equipment/rentals — list of active rentals
| - /admin/equipment/returns/{id} — return inspection page
| - /admin/equipment/payment/{id} — payment page
|
| Management:
| - /admin/equipment-types — manage chairs/tables prices
| - /admin/cottages — manage cottage prices
|
*/



/*
|==========================================================================
| EQUIPMENT WALK-IN RENTAL
|==========================================================================
*/

Route::get('/admin/equipment/walkin', function (SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $equipmentTypes = collect($supabase->getEquipmentTypes());
    $cottages       = collect($supabase->getCottages());

    return view('admin.equipment-walkin', compact('equipmentTypes', 'cottages'));
});

Route::get('/admin/equipment/walkin', function (SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $equipmentTypes = $supabase->getEquipmentTypes();
    $cottages = $supabase->getCottages();

    return view('admin.equipment-walkin', compact('equipmentTypes', 'cottages'));
});

Route::post('/admin/equipment/walkin', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    // VALIDATION
    $request->validate([
        'guest_name'   => 'required|string|max:255',
        'phone'        => 'required|string|max:20',
        'rental_date'  => 'required|date',
        'return_date'  => 'required|date|after:rental_date',
    ]);

    $guestName = $request->guest_name;
    $phone = $request->phone;
    $email = $request->email ?? null;
    $rentalDate = $request->rental_date;
    $returnDate = $request->return_date;

    // Calculate days
    $startDate = new DateTime($rentalDate);
    $endDate = new DateTime($returnDate);
    $days = $endDate->diff($startDate)->days;
    if ($days == 0) $days = 1;

    // ========================================================================
    // COLLECT ITEMS & VALIDATE AVAILABILITY
    // ========================================================================
    $items = [];
    $totalAmount = 0;

    // EQUIPMENT (Chairs, Tables)
    if ($request->has('equipment')) {
        foreach ($request->equipment as $equipmentId => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity <= 0) continue;

            // GET EQUIPMENT
            $equipment = $supabase->getEquipmentTypeById($equipmentId);
            if (!$equipment) {
                return back()->with('error', 'Equipment not found');
            }

            // CHECK AVAILABILITY ✅ THIS IS THE FIX
            $available = (int)($equipment['quantity_available'] ?? 0);
            if ($available < $quantity) {
                return back()->with('error', 
                    $equipment['name'] . ' only has ' . $available . ' available, requested ' . $quantity
                );
            }

            // Calculate subtotal
            $unitPrice = (float)($equipment['unit_price'] ?? 0);
            $subtotal = $unitPrice * $quantity * $days;

            $items[] = [
                'item_type'  => 'equipment',
                'item_id'    => $equipmentId,
                'item_name'  => $equipment['name'],
                'quantity'   => $quantity,
                'unit_price' => $unitPrice,
                'days'       => $days,
                'subtotal'   => $subtotal,
            ];

            $totalAmount += $subtotal;
        }
    }

    // COTTAGES
    if ($request->has('cottages')) {
        foreach ($request->cottages as $cottageId => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity <= 0) continue;

            // GET COTTAGE
            $cottage = $supabase->getCottageById($cottageId);
            if (!$cottage) {
                return back()->with('error', 'Cottage not found');
            }

            // CHECK AVAILABILITY
            $available = (int)($cottage['quantity_available'] ?? 0);
            if ($available < $quantity) {
                return back()->with('error',
                    $cottage['name'] . ' only has ' . $available . ' available, requested ' . $quantity
                );
            }

            // Calculate subtotal
            $pricePerDay = (float)($cottage['price_per_day'] ?? 0);
            $subtotal = $pricePerDay * $quantity * $days;

            $items[] = [
                'item_type'  => 'cottage',
                'item_id'    => $cottageId,
                'item_name'  => $cottage['name'],
                'quantity'   => $quantity,
                'unit_price' => $pricePerDay,
                'days'       => $days,
                'subtotal'   => $subtotal,
            ];

            $totalAmount += $subtotal;
        }
    }

    // CHECK IF ITEMS EXIST
    if (empty($items)) {
        return back()->with('error', 'Please select at least one item to rent');
    }

    // ========================================================================
    // CREATE RENTAL
    // ========================================================================
    $response = $supabase->createEquipmentRental([
        'guest_name'     => $guestName,
        'phone'          => $phone,
        'email'          => $email,
        'rental_date'    => $rentalDate,
        'return_date'    => $returnDate,
        'days'           => $days,
        'total_amount'   => $totalAmount,
        'paid_amount'    => 0,
        'balance_amount' => $totalAmount,
        'payment_status' => 'unpaid',
        'status'         => 'active',
    ]);

    if (!$response || empty($response)) {
        return back()->with('error', 'Failed to create rental');
    }

    $rentalId = $response[0]['id'];

    // ========================================================================
    // ADD ITEMS & DEDUCT INVENTORY ✅
    // ========================================================================
    foreach ($items as $item) {
        // Add rental item
        $supabase->addRentalItem($rentalId, $item);

        // DEDUCT INVENTORY ✅
        if ($item['item_type'] === 'equipment') {
            $supabase->deductEquipmentInventory($item['item_id'], $item['quantity']);
        } elseif ($item['item_type'] === 'cottage') {
            $supabase->deductEquipmentInventory($item['item_id'], $item['quantity']);
        }
    }

    // LOG ACTION
    $supabase->log('equipment_rental_created', [
        'target_type'  => 'equipment_rental',
        'target_id'    => $rentalId,
        'target_label' => $guestName . ' — ' . count($items) . ' item(s)',
        'amount'       => $totalAmount,
    ]);

    return redirect("/admin/equipment/rentals?tab=recent")
        ->with('success', 'Rental created! ₱' . number_format($totalAmount, 2));
});

/*
|==========================================================================
| RENTAL LIST
|==========================================================================
*/

Route::get('/admin/equipment/rentals', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    // GET FILTERS
    $status = $request->get('status', '');
    $paymentStatus = $request->get('payment_status', '');

    // GET ALL RENTALS
    $allRentals = $supabase->getEquipmentRentals();

    // FILTER BY STATUS
    if ($status) {
        $allRentals = array_filter($allRentals, function ($r) use ($status) {
            return ($r['status'] ?? '') === $status;
        });
    }

    // FILTER BY PAYMENT STATUS
    if ($paymentStatus) {
        $allRentals = array_filter($allRentals, function ($r) use ($paymentStatus) {
            return ($r['payment_status'] ?? '') === $paymentStatus;
        });
    }

    // GET ITEMS FOR EACH RENTAL ✅
    $rentals = [];
    foreach ($allRentals as $rental) {
        $rental['items'] = $supabase->getRentalItems($rental['id']);
        $rentals[] = $rental;
    }

    return view('admin.equipment-rentals', compact('rentals', 'supabase'));
});


/*
|==========================================================================
| PAYMENT PAGE
|==========================================================================
*/

Route::get('/admin/equipment/payment/{id}', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $rental = $supabase->getEquipmentRentalById($id);
    if (!$rental) return back()->with('error', 'Rental not found.');

    $rental['items'] = $supabase->getRentalItems($id);

    return view('admin.equipment-payment', compact('rental'));
});



/*
|==========================================================================
| EQUIPMENT PAYMENT ROUTE - CASH ONLY, FULL PAYMENT ONLY
|==========================================================================
*/


Route::get('/admin/equipment/payment/{id}', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $rental = $supabase->getEquipmentRentalById($id);
    if (!$rental) return back()->with('error', 'Rental not found');

    return view('admin.equipment-payment', compact('rental', 'supabase'));
});

Route::post('/admin/equipment/payment/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    // VALIDATE
    $validated = $request->validate([
        'cash_received' => 'required|numeric|min:0.01',
    ]);

    // GET RENTAL
    $rental = $supabase->getEquipmentRentalById($id);
    if (!$rental) {
        return back()->withErrors(['payment' => 'Rental not found']);
    }

    $cash = (float)$validated['cash_received'];
    $total = (float)($rental['total_amount'] ?? 0);
    $alreadyPaid = (float)($rental['paid_amount'] ?? 0);
    $balance = $total - $alreadyPaid;

    // VALIDATE AMOUNT - MUST BE FULL PAYMENT
    if ($cash <= 0) {
        return back()->withErrors(['payment' => 'Cash received must be greater than 0']);
    }

    // FULL PAYMENT ONLY - NO PARTIAL
    if ($cash < $balance) {
        return back()->withErrors(['payment' => 'Must pay full balance of ₱' . number_format($balance, 2)]);
    }

    $newPaid = $total;
    $newBalance = 0;
    $newStatus = 'paid';

    // ========================================================================
    // UPDATE RENTAL — INCLUDE ALL REQUIRED FIELDS
    // ========================================================================
    $updateData = [
        'guest_name'      => $rental['guest_name'],
        'phone'           => $rental['phone'],
        'email'           => $rental['email'],
        'rental_date'     => $rental['rental_date'],
        'return_date'     => $rental['return_date'],
        'days'            => $rental['days'],
        'total_amount'    => $rental['total_amount'],
        'paid_amount'     => $newPaid,
        'balance_amount'  => $newBalance,
        'payment_status'  => $newStatus,
        'status'          => $rental['status'],
        'notes'           => $rental['notes'],
    ];
    
    $updateResponse = $supabase->updateEquipmentRental($id, $updateData);
    
    if (!$updateResponse || empty($updateResponse)) {
        return back()->withErrors(['payment' => 'Failed to update payment. Try again.']);
    }

    // ========================================================================
    // RECORD PAYMENT IN payment_records TABLE
    // ========================================================================
    $paymentResponse = $supabase->recordPayment([
        'target_type'     => 'equipment_rental',
        'target_id'       => (string)$id,
        'guest_name'      => $rental['guest_name'] ?? 'Unknown',
        'room_info'       => 'Equipment rental - Full',
        'amount_received' => $cash,
        'payment_type'    => 'full',
        'payment_method'  => 'cash',
        'total_amount'    => $total,
        'balance_after'   => 0,
    ]);

    // ========================================================================
    // LOG ACTION
    // ========================================================================
    $supabase->log('equipment_payment_received', [
        'target_type'  => 'equipment_rental',
        'target_id'    => (string)$id,
        'target_label' => $rental['guest_name'] . ' — ₱' . number_format($cash, 2) . ' (cash)',
        'amount'       => $cash,
        'payment_type' => 'full',
    ]);

    return redirect('/admin/equipment/rentals')
        ->with('success', '✅ Full payment recorded! ₱' . number_format($cash, 2) . ' (cash)');
});



/*
|==========================================================================
| RETURN INSPECTION
|==========================================================================
*/

Route::get('/admin/equipment/return/{id}', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $rental = $supabase->getEquipmentRentalById($id);
    if (!$rental) return back()->with('error', 'Rental not found.');

    $rental['items'] = $supabase->getRentalItems($id);
    $rental['return'] = $supabase->getRentalReturn($id);

    return view('admin.equipment-return', compact('rental', 'supabase'));
});


Route::post('/admin/equipment/return/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $rental = $supabase->getEquipmentRentalById($id);
    if (!$rental) {
        return back()->withErrors(['return' => 'Rental not found.']);
    }

    // VALIDATE
    $validated = $request->validate([
        'returned_date' => 'nullable|date',
        'returned_time' => 'nullable|string',
        'condition'     => 'required|in:good,damaged,missing',
        'damage_description' => 'nullable|string',
        'damage_amount' => 'nullable|numeric|min:0',
        'notes'         => 'nullable|string',
    ]);

    $items = $supabase->getRentalItems($id);
    $damageAmount = (float)($validated['damage_amount'] ?? 0);

    // ========================================================================
    // CHECK IF RETURN ALREADY RECORDED
    // ========================================================================
    $existingReturn = $supabase->getRentalReturn($id);

    if ($existingReturn) {
        // UPDATE EXISTING RETURN
        $updateReturn = $supabase->updateRentalReturn($existingReturn['id'], [
            'condition'          => $validated['condition'] ?? 'good',
            'damage_description' => $validated['damage_description'] ?? null,
            'damage_amount'      => $damageAmount,
            'notes'              => $validated['notes'] ?? null,
        ]);

        if (!$updateReturn) {
            return back()->withErrors(['return' => 'Failed to update return record']);
        }
    } else {
        // CREATE NEW RETURN RECORD
        $createReturn = $supabase->recordRentalReturn($id, [
            'returned_date'      => $validated['returned_date'] ?? date('Y-m-d'),
            'returned_time'      => $validated['returned_time'] ?? null,
            'condition'          => $validated['condition'] ?? 'good',
            'damage_description' => $validated['damage_description'] ?? null,
            'damage_amount'      => $damageAmount,
            'notes'              => $validated['notes'] ?? null,
            'returned_by'        => $request->returned_by ?? null,
        ]);

        if (!$createReturn) {
            return back()->withErrors(['return' => 'Failed to create return record']);
        }
    }

    // ========================================================================
    // RESTORE INVENTORY when equipment is returned ✅
    // ========================================================================
    foreach ($items as $item) {
        if ($item['item_type'] === 'equipment') {
            $supabase->restoreEquipmentInventory($item['item_id'], $item['quantity']);
        }
    }

    // ========================================================================
    // IF THERE ARE DAMAGES, UPDATE BALANCE
    // ========================================================================
    if ($damageAmount > 0) {
        $newBalance = (float)($rental['balance_amount'] ?? 0) + $damageAmount;
        
        $updateBalanceData = [
            'guest_name'      => $rental['guest_name'],
            'phone'           => $rental['phone'],
            'email'           => $rental['email'],
            'rental_date'     => $rental['rental_date'],
            'return_date'     => $rental['return_date'],
            'days'            => $rental['days'],
            'total_amount'    => $rental['total_amount'],
            'paid_amount'     => $rental['paid_amount'],
            'balance_amount'  => $newBalance,
            'payment_status'  => $newBalance > 0 ? 'partial' : 'paid',
            'status'          => 'returned',
            'notes'           => $rental['notes'],
        ];

        $supabase->updateEquipmentRental($id, $updateBalanceData);

        $supabase->log('equipment_damage_recorded', [
            'target_type'  => 'equipment_rental',
            'target_id'    => $id,
            'target_label' => $rental['guest_name'] . ' — Damage charge: ₱' . number_format($damageAmount, 2),
            'amount'       => $damageAmount,
        ]);
    } else {
        // NO DAMAGES - JUST UPDATE STATUS
        $updateStatusData = [
            'guest_name'      => $rental['guest_name'],
            'phone'           => $rental['phone'],
            'email'           => $rental['email'],
            'rental_date'     => $rental['rental_date'],
            'return_date'     => $rental['return_date'],
            'days'            => $rental['days'],
            'total_amount'    => $rental['total_amount'],
            'paid_amount'     => $rental['paid_amount'],
            'balance_amount'  => $rental['balance_amount'],
            'payment_status'  => $rental['payment_status'],
            'status'          => 'returned',
            'notes'           => $rental['notes'],
        ];

        $supabase->updateEquipmentRental($id, $updateStatusData);
    }

    // ========================================================================
    // LOG THE RETURN
    // ========================================================================
    $supabase->log('equipment_rental_returned', [
        'target_type'  => 'equipment_rental',
        'target_id'    => $id,
        'target_label' => $rental['guest_name'] . ' — ' . count($items) . ' item(s) returned (' . ($validated['condition'] ?? 'good') . ')',
    ]);

    return redirect('/admin/equipment/rentals')
        ->with('success', '✅ Return inspection recorded + inventory restored! Items: ' . count($items));
});
/*
|==========================================================================
| EQUIPMENT TYPES MANAGEMENT (Chairs, Tables)
|==========================================================================
*/

Route::get('/admin/equipment-types', function (SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $equipmentTypes = collect($supabase->getEquipmentTypes());

    return view('admin.equipment-types', compact('equipmentTypes'));
});

Route::post('/admin/equipment-types/create', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $supabase->createEquipmentType([
        'name'                 => $request->name,
        'unit_price'           => (float)$request->unit_price,
        'quantity_available'   => (int)$request->quantity_available,
        'is_active'            => true,
    ]);

    return back()->with('success', 'Equipment type created.');
});

Route::post('/admin/equipment-types/update/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $supabase->updateEquipmentType($id, [
        'name'                 => $request->name,
        'unit_price'           => (float)$request->unit_price,
        'quantity_available'   => (int)$request->quantity_available,
    ]);

    return back()->with('success', 'Equipment type updated.');
});

/*
|==========================================================================
| COTTAGES MANAGEMENT
|==========================================================================
*/

Route::get('/admin/cottages', function (SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $cottages = collect($supabase->getCottages());

    return view('admin.cottages', compact('cottages'));
});

Route::post('/admin/cottages/create', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $supabase->createCottage([
        'name'               => $request->name,
        'size_category'      => $request->size_category,
        'price_per_day'      => (float)$request->price_per_day,
        'quantity_available' => 1,
        'description'        => $request->description ?? null,
        'is_active'          => true,
    ]);

    return back()->with('success', 'Cottage added.');
});

Route::post('/admin/cottages/update/{id}', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $supabase->updateCottage($id, [
        'name'               => $request->name,
        'size_category'      => $request->size_category,
        'price_per_day'      => (float)$request->price_per_day,
        'description'        => $request->description ?? null,
    ]);

    return back()->with('success', 'Cottage updated.');
});

Route::get('/admin/cottages/delete/{id}', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $supabase->deleteCottage($id);

    return back()->with('success', 'Cottage deleted.');
});

/*
|==========================================================================
| CASHIER — SUBMIT DAILY PAYMENTS
|==========================================================================
*/

Route::get('/admin/payment-submit', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $date = $request->get('date', date('Y-m-d'));
    $staffId = session('admin_id');
    
    // Get all payments for this staff today
    $allPayments = collect($supabase->getPaymentRecords());
    $todayPayments = $allPayments->filter(function ($p) use ($date, $staffId) {
        $pDate = date('Y-m-d', strtotime($p['received_at'] ?? now()));
        return $pDate === $date && $p['staff_id'] == $staffId;
    });

    // Calculate totals
    $totalCash = $todayPayments->sum('amount_received');
    $paymentCount = $todayPayments->count();
    
    // Check if already submitted
    $submitted = collect($supabase->getPaymentSubmissions([
        'staff_id' => $staffId,
        'date' => $date,
    ]))->first();

    return view('admin.payment-submit', compact(
        'date', 'todayPayments', 'totalCash', 'paymentCount', 'submitted'
    ));
});

Route::post('/admin/payment-submit', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $date = $request->get('date', date('Y-m-d'));
    $staffId = session('admin_id');

    // Get today's payments
    $allPayments = collect($supabase->getPaymentRecords());
    $todayPayments = $allPayments->filter(function ($p) use ($date, $staffId) {
        $pDate = date('Y-m-d', strtotime($p['received_at'] ?? now()));
        return $pDate === $date && $p['staff_id'] == $staffId;
    });

    if ($todayPayments->isEmpty()) {
        return back()->with('error', 'No payments to submit for today.');
    }

    $totalCash = $todayPayments->sum('amount_received');
    $paymentCount = $todayPayments->count();

    // Create submission
    $response = $supabase->createPaymentSubmission([
        'staff_id'        => $staffId,
        'staff_name'      => session('admin_name'),
        'submission_date' => $date,
        'total_cash'      => $totalCash,
        'payment_count'   => $paymentCount,
        'notes'           => $request->notes ?? null,
    ]);

    if (!$response || empty($response)) {
        return back()->with('error', 'Submission failed.');
    }

    $submissionId = $response[0]['id'];

    // Add payment items
    foreach ($todayPayments as $payment) {
        $supabase->addSubmissionItem($submissionId, [
            'payment_record_id' => $payment['id'] ?? null,
            'target_type'       => $payment['target_type'],
            'target_id'         => $payment['target_id'],
            'guest_name'        => $payment['guest_name'],
            'amount'            => $payment['amount_received'],
        ]);
    }

    $supabase->log('payment_submission', [
        'target_type'  => 'payment_submission',
        'target_id'    => $submissionId,
        'target_label' => 'Payment submission for ' . $date . ' — ₱' . number_format($totalCash, 2),
        'amount'       => $totalCash,
    ]);

    return redirect('/admin/payment-submit')->with('success', 'Payment submission created! Awaiting admin approval.');
});

/*
|==========================================================================
| ADMIN — APPROVE/REJECT SUBMISSIONS
|==========================================================================
*/

Route::get('/admin/payment-submissions', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard');

    $status = $request->get('status', 'pending');
    $date = $request->get('date', '');

    $filters = ['status' => $status];
    if ($date) $filters['date'] = $date;

    $submissions = collect($supabase->getPaymentSubmissions($filters));
    $staff = collect($supabase->getStaff());

    return view('admin.payment-submissions', compact('submissions', 'staff', 'status', 'date'));
});

Route::get('/admin/payment-submission/{id}', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard');

    $submission = $supabase->getPaymentSubmissionById($id);
    if (!$submission) return back()->with('error', 'Not found');

    $items = $supabase->getSubmissionItems($id);

    return view('admin.payment-submission-detail', compact('submission', 'items'));
});

Route::post('/admin/payment-submission/{id}/approve', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard');

    $submission = $supabase->getPaymentSubmissionById($id);
    if (!$submission) return back()->with('error', 'Not found');

    $supabase->updatePaymentSubmission($id, [
        'status'      => 'approved',
        'admin_id'    => session('admin_id'),
        'admin_name'  => session('admin_name'),
        'approved_at' => now()->toISOString(),
        'notes'       => $request->notes ?? null,
    ]);

    $supabase->log('payment_submission_approved', [
        'target_type'  => 'payment_submission',
        'target_id'    => $id,
        'target_label' => $submission['staff_name'] . ' — ₱' . number_format($submission['total_cash'], 2) . ' APPROVED',
        'amount'       => $submission['total_cash'],
    ]);

    return back()->with('success', 'Payment submission approved! ✅');
});

Route::post('/admin/payment-submission/{id}/reject', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard');

    $submission = $supabase->getPaymentSubmissionById($id);
    if (!$submission) return back()->with('error', 'Not found');

    $supabase->updatePaymentSubmission($id, [
        'status'   => 'rejected',
        'notes'    => $request->notes ?? null,
    ]);

    $supabase->log('payment_submission_rejected', [
        'target_type'  => 'payment_submission',
        'target_id'    => $id,
        'target_label' => $submission['staff_name'] . ' — ₱' . number_format($submission['total_cash'], 2) . ' REJECTED',
        'amount'       => $submission['total_cash'],
    ]);

    return back()->with('success', 'Payment submission rejected.');
});

/*
|==========================================================================
| REPORTS — Daily Remittance Report
|==========================================================================
*/

Route::get('/admin/remittance-report', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    if (session('admin_role') !== 'admin') return redirect('/admin/dashboard');

    $date = $request->get('date', date('Y-m-d'));

    $submissions = collect($supabase->getPaymentSubmissions(['date' => $date]));
    $staff = collect($supabase->getStaff());

    // Summary
    $totalSubmitted = $submissions->sum('total_cash');
    $totalApproved = $submissions->where('status', 'approved')->sum('total_cash');
    $totalPending = $submissions->where('status', 'pending')->sum('total_cash');
    $approvedCount = $submissions->where('status', 'approved')->count();

    return view('admin.remittance-report', compact(
        'date', 'submissions', 'staff',
        'totalSubmitted', 'totalApproved', 'totalPending', 'approvedCount'
    ));
});

/*
|==========================================================================
| COTTAGE BOOKING - COMPLETE ROUTES
| Add these to routes/web.php INSTEAD of the previous routes
|==========================================================================
*/

// API - GET AVAILABLE COTTAGES FOR DATES
Route::get('/api/available-cottages', function (Request $request, SupabaseService $supabase) {
    $checkIn = $request->get('check_in');
    $checkOut = $request->get('check_out');

    if (!$checkIn || !$checkOut) {
        return response()->json(['available' => []]);
    }

    $allCottages = collect($supabase->getCottages())->where('is_active', true);
    $bookings = collect($supabase->getCottageBookings())
        ->filter(fn($b) => in_array($b['booking_status'], ['confirmed', 'checked_in']));

    $available = [];
    foreach ($allCottages as $cottage) {
        $isBooked = $bookings->contains(function ($b) use ($cottage, $checkIn, $checkOut) {
            if ($b['cottage_id'] != $cottage['id']) return false;
            return $checkIn < $b['check_out'] && $checkOut > $b['check_in'];
        });

        if (!$isBooked) {
            $available[] = $cottage;
        }
    }

    return response()->json(['available' => $available]);
});

// ADMIN - NEW COTTAGE BOOKING
Route::get('/admin/cottage/booking', function (SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    return view('admin.cottage-booking', compact('supabase'));
});

Route::post('/admin/cottage/booking', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $request->validate([
        'guest_name'  => 'required|string|max:255',
        'guest_phone' => 'required|string|max:20',
        'check_in'    => 'required|date|after_or_equal:today',
        'check_out'   => 'required|date|after:check_in',
        'cottage_id'  => 'required|integer',
    ]);

    $cottageId = (int)$request->cottage_id;
    $checkIn = $request->check_in;
    $checkOut = $request->check_out;

    // CHECK AVAILABILITY
    $allBookings = collect($supabase->getCottageBookings())
        ->filter(fn($b) => in_array($b['booking_status'], ['confirmed', 'checked_in']));

    $isBooked = $allBookings->contains(function ($b) use ($cottageId, $checkIn, $checkOut) {
        if ($b['cottage_id'] != $cottageId) return false;
        return $checkIn < $b['check_out'] && $checkOut > $b['check_in'];
    });

    if ($isBooked) {
        return back()->withErrors(['cottage' => 'Cottage is booked for those dates']);
    }

    // CALCULATE
    $startDate = new DateTime($checkIn);
    $endDate = new DateTime($checkOut);
    $nights = $endDate->diff($startDate)->days;
    if ($nights == 0) $nights = 1;

    $cottage = $supabase->getCottageById($cottageId);
    $pricePerNight = (float)($cottage['price_per_day'] ?? 0);
    $totalAmount = $pricePerNight * $nights;

    // CREATE BOOKING
    $response = $supabase->createCottageBooking([
        'cottage_id'       => $cottageId,
        'guest_name'       => $request->guest_name,
        'guest_email'      => $request->guest_email ?? null,
        'guest_phone'      => $request->guest_phone,
        'check_in'         => $checkIn,
        'check_out'        => $checkOut,
        'number_of_nights' => $nights,
        'price_per_night'  => $pricePerNight,
        'total_amount'     => $totalAmount,
        'paid_amount'      => 0,
        'balance_amount'   => $totalAmount,
        'payment_status'   => 'unpaid',
        'booking_status'   => 'confirmed',
        'notes'            => $request->notes ?? null,
    ]);

    if (!$response || empty($response)) {
        return back()->with('error', 'Failed to create booking');
    }

    $bookingId = $response[0]['id'];

    $supabase->log('cottage_booking_created', [
        'target_type'  => 'cottage_booking',
        'target_id'    => $bookingId,
        'target_label' => $request->guest_name . ' — ' . $cottage['name'] . ' (' . $nights . ' night' . ($nights > 1 ? 's' : '') . ')',
        'amount'       => $totalAmount,
    ]);

    return redirect('/admin/cottage/bookings')
        ->with('success', '✅ Booking created! ₱' . number_format($totalAmount, 2) . ' for ' . $nights . ' night(s)');
});

// ADMIN - VIEW ALL BOOKINGS
Route::get('/admin/cottage/bookings', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $allBookings = collect($supabase->getCottageBookings());
    $cottages = collect($supabase->getCottages());
    $today = date('Y-m-d');

    $filter = $request->get('filter', 'upcoming');

    if ($filter === 'upcoming') {
        $bookings = $allBookings->where('booking_status', 'confirmed')
                                 ->where('check_in', '>=', $today)
                                 ->sortBy('check_in')
                                 ->values();
    } elseif ($filter === 'checked_in') {
        $bookings = $allBookings->where('booking_status', 'checked_in')->values();
    } elseif ($filter === 'unpaid') {
        $bookings = $allBookings->where('payment_status', 'unpaid')->values();
    } else {
        $bookings = $allBookings->sortByDesc('created_at')->values();
    }

    return view('admin.cottage-bookings', compact('bookings', 'cottages', 'filter', 'supabase'));
});

// ADMIN - BOOKING DETAIL
Route::get('/admin/cottage/booking/{id}', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $booking = $supabase->getCottageBookingById($id);
    if (!$booking) return back()->with('error', 'Booking not found');

    $cottage = $supabase->getCottageById($booking['cottage_id']);
    $payments = $supabase->getCottageBookingPayments($id);

    return view('admin.cottage-booking-detail', compact('booking', 'cottage', 'payments', 'supabase'));
});

// ADMIN - RECORD PAYMENT
Route::post('/admin/cottage/booking/{id}/payment', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $request->validate(['cash_received' => 'required|numeric|min:0.01']);

    $booking = $supabase->getCottageBookingById($id);
    if (!$booking) return back()->with('error', 'Booking not found');

    $cash = (float)$request->cash_received;
    $total = (float)$booking['total_amount'];
    $alreadyPaid = (float)($booking['paid_amount'] ?? 0);
    $balance = $total - $alreadyPaid;

    if ($cash <= 0) return back()->with('error', 'Invalid amount');
    if ($cash < $balance) return back()->with('error', 'Need ₱' . number_format($balance, 2));

    $supabase->updateCottageBooking($id, [
        'guest_name'       => $booking['guest_name'],
        'guest_email'      => $booking['guest_email'],
        'guest_phone'      => $booking['guest_phone'],
        'check_in'         => $booking['check_in'],
        'check_out'        => $booking['check_out'],
        'number_of_nights' => $booking['number_of_nights'],
        'price_per_night'  => $booking['price_per_night'],
        'total_amount'     => $booking['total_amount'],
        'paid_amount'      => $total,
        'balance_amount'   => 0,
        'payment_status'   => 'paid',
        'booking_status'   => $booking['booking_status'],
        'notes'            => $booking['notes'],
    ]);

    $supabase->recordCottagePayment($id, ['amount_received' => $cash]);

    $supabase->log('cottage_payment', [
        'target_type'  => 'cottage_booking',
        'target_id'    => $id,
        'target_label' => $booking['guest_name'] . ' — ₱' . number_format($cash, 2),
        'amount'       => $cash,
    ]);

    return back()->with('success', '✅ Payment recorded! ₱' . number_format($cash, 2));
});

// ADMIN - CHECK-IN
Route::post('/admin/cottage/booking/{id}/checkin', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $booking = $supabase->getCottageBookingById($id);
    if (!$booking) return back()->with('error', 'Booking not found');

    $supabase->updateCottageBooking($id, [
        'guest_name'       => $booking['guest_name'],
        'guest_email'      => $booking['guest_email'],
        'guest_phone'      => $booking['guest_phone'],
        'check_in'         => $booking['check_in'],
        'check_out'        => $booking['check_out'],
        'number_of_nights' => $booking['number_of_nights'],
        'price_per_night'  => $booking['price_per_night'],
        'total_amount'     => $booking['total_amount'],
        'paid_amount'      => $booking['paid_amount'],
        'balance_amount'   => $booking['balance_amount'],
        'payment_status'   => $booking['payment_status'],
        'booking_status'   => 'checked_in',
        'notes'            => $booking['notes'],
    ]);

    $supabase->log('cottage_checkin', [
        'target_type'  => 'cottage_booking',
        'target_id'    => $id,
        'target_label' => $booking['guest_name'],
    ]);

    return back()->with('success', '✅ Checked in');
});

// ADMIN - CHECK-OUT
Route::post('/admin/cottage/booking/{id}/checkout', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');

    $booking = $supabase->getCottageBookingById($id);
    if (!$booking) return back()->with('error', 'Booking not found');

    $supabase->updateCottageBooking($id, [
        'guest_name'       => $booking['guest_name'],
        'guest_email'      => $booking['guest_email'],
        'guest_phone'      => $booking['guest_phone'],
        'check_in'         => $booking['check_in'],
        'check_out'        => $booking['check_out'],
        'number_of_nights' => $booking['number_of_nights'],
        'price_per_night'  => $booking['price_per_night'],
        'total_amount'     => $booking['total_amount'],
        'paid_amount'      => $booking['paid_amount'],
        'balance_amount'   => $booking['balance_amount'],
        'payment_status'   => $booking['payment_status'],
        'booking_status'   => 'checked_out',
        'notes'            => $booking['notes'],
    ]);

    $supabase->log('cottage_checkout', [
        'target_type'  => 'cottage_booking',
        'target_id'    => $id,
        'target_label' => $booking['guest_name'],
    ]);

    return back()->with('success', '✅ Checked out');
});

// SHOW POS FORM
Route::get('/admin/walkin/daytour', function (SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $dayTourPackages = $supabase->getDayTourPackages();
    $cottages = $supabase->getCottages();
    $equipmentTypes = $supabase->getEquipmentTypes();
    
    return view('admin.walkin-daytour-pos', compact('dayTourPackages', 'cottages', 'equipmentTypes'));
});

// CREATE TRANSACTION (Multi-Package)
Route::post('/admin/walkin/daytour/store', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $request->validate([
        'guest_name'  => 'required|string|max:255',
        'guest_phone' => 'required|string|max:20',
        'items_json'  => 'required|json',
    ]);
    
    $itemsData = json_decode($request->items_json, true);
    
    if (empty($itemsData['packages']) && empty($itemsData['cottages']) && empty($itemsData['equipment'])) {
        return back()->withErrors(['items' => 'Please add at least one item']);
    }
    
    // CALCULATE TOTAL
    $total = 0;
    foreach ($itemsData['packages'] as $pkg) {
        $total += (float)($pkg['subtotal'] ?? 0);
    }
    foreach ($itemsData['cottages'] as $cottage) {
        $total += (float)($cottage['subtotal'] ?? 0);
    }
    foreach ($itemsData['equipment'] as $eq) {
        $total += (float)($eq['subtotal'] ?? 0);
    }
    
    // CREATE TRANSACTION ID
    $transactionId = $supabase->generateTransactionId('TOUR');
    
    // CREATE WALK-IN DAY TOUR HEADER
    $tourResponse = $supabase->createWalkInDayTour([
        'transaction_id' => $transactionId,
        'guest_name'     => $request->guest_name,
        'guest_phone'    => $request->guest_phone,
        'guest_email'    => $request->guest_email ?? null,
        'total_guests'   => 0,
        'total_amount'   => $total,
        'paid_amount'    => 0,
        'balance_amount' => $total,
        'payment_status' => 'unpaid',
        'notes'          => $request->notes ?? null,
    ]);
    
    if (!$tourResponse || empty($tourResponse)) {
        return back()->withErrors(['error' => 'Failed to create transaction']);
    }
    
    $tourId = $tourResponse[0]['id'];
    
    // ADD PACKAGE ITEMS
    foreach ($itemsData['packages'] as $pkg) {
        $supabase->addDayTourItem($tourId, [
            'item_type'      => 'package',
            'item_id'        => $pkg['pkgId'],
            'item_name'      => $pkg['name'],
            'guest_count'    => $pkg['guestCount'],
            'price_per_unit' => $pkg['pricePerUnit'],
            'quantity'       => 1,
            'subtotal'       => $pkg['subtotal'],
        ]);
    }
    
    // ADD COTTAGE ITEMS
    foreach ($itemsData['cottages'] as $cottage) {
        if (!empty($cottage['cottageId'])) {
            $supabase->addDayTourItem($tourId, [
                'item_type'      => 'cottage',
                'item_id'        => $cottage['cottageId'],
                'item_name'      => $cottage['name'],
                'guest_count'    => 1,
                'price_per_unit' => $cottage['pricePerNight'],
                'quantity'       => $cottage['nights'],
                'subtotal'       => $cottage['subtotal'],
            ]);
        }
    }
    
    // ADD EQUIPMENT ITEMS
    foreach ($itemsData['equipment'] as $eq) {
        if (!empty($eq['equipmentId'])) {
            $supabase->addDayTourItem($tourId, [
                'item_type'      => 'equipment',
                'item_id'        => $eq['equipmentId'],
                'item_name'      => $eq['name'],
                'guest_count'    => 1,
                'price_per_unit' => $eq['pricePerUnit'],
                'quantity'       => $eq['quantity'],
                'subtotal'       => $eq['subtotal'],
            ]);
        }
    }
    
    // LOG ACTION
    $supabase->log('walkin_daytour_created', [
        'target_type'  => 'walk_in_day_tour',
        'target_id'    => $tourId,
        'target_label' => $request->guest_name . ' — ' . $transactionId . ' (₱' . number_format($total, 2) . ')',
        'amount'       => $total,
    ]);
    
    return redirect("/admin/walkin/daytour/$tourId/payment")
        ->with('success', '✅ Transaction created! ₱' . number_format($total, 2));
});

// PAYMENT PAGE
Route::get('/admin/walkin/daytour/{id}/payment', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $tour = $supabase->getDayTourWithItems($id);
    if (!$tour) return back()->with('error', 'Tour not found');
    
    $payments = $supabase->getWalkInPayments($tour['transaction_id']);
    
    return view('admin.walkin-daytour-payment', compact('tour', 'payments', 'supabase'));
});

// RECORD PAYMENT
Route::post('/admin/walkin/daytour/{id}/payment', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $request->validate(['cash_received' => 'required|numeric|min:0.01']);
    
    $tour = $supabase->getDayTourWithItems($id);
    if (!$tour) return back()->with('error', 'Tour not found');
    
    $cash = (float)$request->cash_received;
    $total = (float)$tour['total_amount'];
    $alreadyPaid = (float)($tour['paid_amount'] ?? 0);
    $balance = $total - $alreadyPaid;
    
    if ($cash <= 0) return back()->with('error', 'Invalid amount');
    if ($cash > $balance) $cash = $balance;
    
    $newPaid = $alreadyPaid + $cash;
    $newBalance = $total - $newPaid;
    $newStatus = ($newBalance <= 0) ? 'paid' : 'partial';
    
    // UPDATE TOUR
    $supabase->updateWalkInDayTour($id, [
        'paid_amount'    => $newPaid,
        'balance_amount' => $newBalance,
        'payment_status' => $newStatus,
    ]);
    
    // RECORD PAYMENT
    $supabase->recordWalkInPayment([
        'transaction_id'   => $tour['transaction_id'],
        'transaction_type' => 'day_tour',
        'parent_id'        => $id,
        'guest_name'       => $tour['guest_name'],
        'amount_received'  => $cash,
        'payment_method'   => 'cash',
        'payment_type'     => $newStatus === 'paid' ? 'full' : 'partial',
    ]);
    
    $supabase->log('walkin_daytour_payment', [
        'target_type'  => 'walk_in_day_tour',
        'target_id'    => $id,
        'target_label' => $tour['guest_name'] . ' — ₱' . number_format($cash, 2),
        'amount'       => $cash,
    ]);
    
    return back()->with('success', '✅ Payment recorded! ₱' . number_format($cash, 2));
});

// VIEW ALL DAY TOURS
Route::get('/admin/walkin/daytours', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $allTours = collect($supabase->getCottageBookings());
    $filter = $request->get('filter', 'recent');
    
    if ($filter === 'unpaid') {
        $tours = $allTours->where('payment_status', 'unpaid')->sortByDesc('created_at')->values();
    } else {
        $tours = $allTours->sortByDesc('created_at')->values();
    }
    
    return view('admin.walkin-daytours-list', compact('tours', 'filter'));
});

// RECEIPT/RECEIPT VIEW
Route::get('/admin/walkin/daytour/{id}/receipt', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $tour = $supabase->getDayTourWithItems($id);
    if (!$tour) return back()->with('error', 'Tour not found');
    
    return view('admin.walkin-daytour-receipt', compact('tour'));
});

Route::get('/admin/walkin/booking', function (SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $rooms = $supabase->getRooms();
    $cottages = $supabase->getCottages();
    $equipmentTypes = $supabase->getEquipmentTypes();
    
    return view('admin.walkin-booking-pos', compact('rooms', 'cottages', 'equipmentTypes'));
});

// CREATE BOOKING (Multi-Item)
Route::post('/admin/walkin/booking/store', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $request->validate([
        'guest_name'  => 'required|string|max:255',
        'guest_phone' => 'required|string|max:20',
        'check_in'    => 'required|date|after_or_equal:today',
        'check_out'   => 'required|date|after:check_in',
        'items_json'  => 'required|json',
    ]);
    
    $itemsData = json_decode($request->items_json, true);
    
    if (empty($itemsData['rooms']) && empty($itemsData['cottages']) && empty($itemsData['equipment'])) {
        return back()->withErrors(['items' => 'Please add at least one item']);
    }
    
    $checkIn = $request->check_in;
    $checkOut = $request->check_out;
    
    // CALCULATE NIGHTS
    $startDate = new DateTime($checkIn);
    $endDate = new DateTime($checkOut);
    $nights = $endDate->diff($startDate)->days;
    if ($nights == 0) $nights = 1;
    
    // CALCULATE TOTAL
    $total = 0;
    foreach ($itemsData['rooms'] as $room) {
        $total += (float)($room['subtotal'] ?? 0);
    }
    foreach ($itemsData['cottages'] as $cottage) {
        $total += (float)($cottage['subtotal'] ?? 0);
    }
    foreach ($itemsData['equipment'] as $eq) {
        $total += (float)($eq['subtotal'] ?? 0);
    }
    
    // CREATE TRANSACTION ID
    $transactionId = $supabase->generateTransactionId('BOOK');
    
    // CREATE WALK-IN BOOKING HEADER
    $bookingResponse = $supabase->createWalkInBooking([
        'transaction_id'   => $transactionId,
        'guest_name'       => $request->guest_name,
        'guest_phone'      => $request->guest_phone,
        'guest_email'      => $request->guest_email ?? null,
        'check_in'         => $checkIn,
        'check_out'        => $checkOut,
        'number_of_nights' => $nights,
        'total_amount'     => $total,
        'paid_amount'      => 0,
        'balance_amount'   => $total,
        'payment_status'   => 'unpaid',
        'booking_status'   => 'confirmed',
        'notes'            => $request->notes ?? null,
    ]);
    
    if (!$bookingResponse || empty($bookingResponse)) {
        return back()->withErrors(['error' => 'Failed to create booking']);
    }
    
    $bookingId = $bookingResponse[0]['id'];
    
    // ADD ROOM ITEMS
    foreach ($itemsData['rooms'] as $room) {
        if (!empty($room['roomId'])) {
            $supabase->addBookingItem($bookingId, [
                'item_type'        => 'room',
                'item_id'          => $room['roomId'],
                'item_name'        => $room['name'],
                'number_of_nights' => $room['nights'],
                'price_per_night'  => $room['pricePerNight'],
                'quantity'         => 1,
                'subtotal'         => $room['subtotal'],
            ]);
        }
    }
    
    // ADD COTTAGE ITEMS
    foreach ($itemsData['cottages'] as $cottage) {
        if (!empty($cottage['cottageId'])) {
            $supabase->addBookingItem($bookingId, [
                'item_type'        => 'cottage',
                'item_id'          => $cottage['cottageId'],
                'item_name'        => $cottage['name'],
                'number_of_nights' => $cottage['nights'],
                'price_per_night'  => $cottage['pricePerNight'],
                'quantity'         => 1,
                'subtotal'         => $cottage['subtotal'],
            ]);
        }
    }
    
    // ADD EQUIPMENT ITEMS
    foreach ($itemsData['equipment'] as $eq) {
        if (!empty($eq['equipmentId'])) {
            $supabase->addBookingItem($bookingId, [
                'item_type'       => 'equipment',
                'item_id'         => $eq['equipmentId'],
                'item_name'       => $eq['name'],
                'quantity'        => $eq['quantity'],
                'price_per_unit'  => $eq['pricePerUnit'],
                'subtotal'        => $eq['subtotal'],
            ]);
        }
    }
    
    // LOG ACTION
    $supabase->log('walkin_booking_created', [
        'target_type'  => 'walk_in_booking',
        'target_id'    => $bookingId,
        'target_label' => $request->guest_name . ' — ' . $transactionId . ' (' . $nights . ' night' . ($nights > 1 ? 's' : '') . ', ₱' . number_format($total, 2) . ')',
        'amount'       => $total,
    ]);
    
    return redirect("/admin/walkin/booking/$bookingId/payment")
        ->with('success', '✅ Booking created! ₱' . number_format($total, 2) . ' for ' . $nights . ' night(s)');
});

// PAYMENT PAGE
Route::get('/admin/walkin/booking/{id}/payment', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    $payments = $supabase->getWalkInPayments($booking['transaction_id']);
    
    return view('admin.walkin-booking-payment', compact('booking', 'payments', 'supabase'));
});

// RECORD PAYMENT
Route::post('/admin/walkin/booking/{id}/payment', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $request->validate(['cash_received' => 'required|numeric|min:0.01']);
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    $cash = (float)$request->cash_received;
    $total = (float)$booking['total_amount'];
    $alreadyPaid = (float)($booking['paid_amount'] ?? 0);
    $balance = $total - $alreadyPaid;
    
    if ($cash <= 0) return back()->with('error', 'Invalid amount');
    if ($cash > $balance) $cash = $balance;
    
    $newPaid = $alreadyPaid + $cash;
    $newBalance = $total - $newPaid;
    $newStatus = ($newBalance <= 0) ? 'paid' : 'partial';
    
    // UPDATE BOOKING
    $supabase->updateWalkInBooking($id, [
        'number_of_nights' => $booking['number_of_nights'],
        'total_amount'     => $booking['total_amount'],
        'paid_amount'      => $newPaid,
        'balance_amount'   => $newBalance,
        'payment_status'   => $newStatus,
        'booking_status'   => $booking['booking_status'],
    ]);
    
    // RECORD PAYMENT
    $supabase->recordWalkInPayment([
        'transaction_id'   => $booking['transaction_id'],
        'transaction_type' => 'booking',
        'parent_id'        => $id,
        'guest_name'       => $booking['guest_name'],
        'amount_received'  => $cash,
        'payment_method'   => 'cash',
        'payment_type'     => $newStatus === 'paid' ? 'full' : 'partial',
    ]);
    
    $supabase->log('walkin_booking_payment', [
        'target_type'  => 'walk_in_booking',
        'target_id'    => $id,
        'target_label' => $booking['guest_name'] . ' — ₱' . number_format($cash, 2),
        'amount'       => $cash,
    ]);
    
    return back()->with('success', '✅ Payment recorded! ₱' . number_format($cash, 2));
});

// CHECK-IN
Route::post('/admin/walkin/booking/{id}/checkin', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    $supabase->updateWalkInBooking($id, [
        'number_of_nights' => $booking['number_of_nights'],
        'total_amount'     => $booking['total_amount'],
        'paid_amount'      => $booking['paid_amount'],
        'balance_amount'   => $booking['balance_amount'],
        'payment_status'   => $booking['payment_status'],
        'booking_status'   => 'checked_in',
    ]);
    
    $supabase->log('walkin_booking_checkin', [
        'target_type'  => 'walk_in_booking',
        'target_id'    => $id,
        'target_label' => $booking['guest_name'],
    ]);
    
    return back()->with('success', '✅ Guest checked in');
});

// CHECK-OUT
Route::post('/admin/walkin/booking/{id}/checkout', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    $supabase->updateWalkInBooking($id, [
        'number_of_nights' => $booking['number_of_nights'],
        'total_amount'     => $booking['total_amount'],
        'paid_amount'      => $booking['paid_amount'],
        'balance_amount'   => $booking['balance_amount'],
        'payment_status'   => $booking['payment_status'],
        'booking_status'   => 'checked_out',
    ]);
    
    $supabase->log('walkin_booking_checkout', [
        'target_type'  => 'walk_in_booking',
        'target_id'    => $id,
        'target_label' => $booking['guest_name'],
    ]);
    
    return back()->with('success', '✅ Guest checked out');
});

// VIEW ALL BOOKINGS
Route::get('/admin/walkin/bookings', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    // For now, get cottage bookings as placeholder
    $allBookings = $supabase->getCottageBookings() ?? [];
    $filter = $request->get('filter', 'upcoming');
    
    $bookings = collect($allBookings);
    $today = date('Y-m-d');
    
    if ($filter === 'upcoming') {
        $bookings = $bookings->where('booking_status', 'confirmed')
                              ->where('check_in', '>=', $today)
                              ->sortBy('check_in')
                              ->values();
    } elseif ($filter === 'checked_in') {
        $bookings = $bookings->where('booking_status', 'checked_in')->values();
    } elseif ($filter === 'unpaid') {
        $bookings = $bookings->where('payment_status', 'unpaid')->values();
    } else {
        $bookings = $bookings->sortByDesc('created_at')->values();
    }
    
    return view('admin.walkin-bookings-list', compact('bookings', 'filter'));
});

// RECEIPT
Route::get('/admin/walkin/booking/{id}/receipt', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    return view('admin.walkin-booking-receipt', compact('booking'));
});

// SHOW POS FORM
Route::get('/admin/walkin/booking', function (SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $rooms = $supabase->getRooms();
    $cottages = $supabase->getCottages();
    $equipmentTypes = $supabase->getEquipmentTypes();
    
    return view('admin.walkin-booking-pos', compact('rooms', 'cottages', 'equipmentTypes'));
});

// CREATE BOOKING (Multi-Item)
Route::post('/admin/walkin/booking/store', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $request->validate([
        'guest_name'  => 'required|string|max:255',
        'guest_phone' => 'required|string|max:20',
        'check_in'    => 'required|date|after_or_equal:today',
        'check_out'   => 'required|date|after:check_in',
        'items_json'  => 'required|json',
    ]);
    
    $itemsData = json_decode($request->items_json, true);
    
    if (empty($itemsData['rooms']) && empty($itemsData['cottages']) && empty($itemsData['equipment'])) {
        return back()->withErrors(['items' => 'Please add at least one item']);
    }
    
    $checkIn = $request->check_in;
    $checkOut = $request->check_out;
    
    // CALCULATE NIGHTS
    $startDate = new DateTime($checkIn);
    $endDate = new DateTime($checkOut);
    $nights = $endDate->diff($startDate)->days;
    if ($nights == 0) $nights = 1;
    
    // CALCULATE TOTAL
    $total = 0;
    foreach ($itemsData['rooms'] as $room) {
        $total += (float)($room['subtotal'] ?? 0);
    }
    foreach ($itemsData['cottages'] as $cottage) {
        $total += (float)($cottage['subtotal'] ?? 0);
    }
    foreach ($itemsData['equipment'] as $eq) {
        $total += (float)($eq['subtotal'] ?? 0);
    }
    
    // CREATE TRANSACTION ID
    $transactionId = $supabase->generateTransactionId('BOOK');
    
    // CREATE WALK-IN BOOKING HEADER
    $bookingResponse = $supabase->createWalkInBooking([
        'transaction_id'   => $transactionId,
        'guest_name'       => $request->guest_name,
        'guest_phone'      => $request->guest_phone,
        'guest_email'      => $request->guest_email ?? null,
        'check_in'         => $checkIn,
        'check_out'        => $checkOut,
        'number_of_nights' => $nights,
        'total_amount'     => $total,
        'paid_amount'      => 0,
        'balance_amount'   => $total,
        'payment_status'   => 'unpaid',
        'booking_status'   => 'confirmed',
        'notes'            => $request->notes ?? null,
    ]);
    
    if (!$bookingResponse || empty($bookingResponse)) {
        return back()->withErrors(['error' => 'Failed to create booking']);
    }
    
    $bookingId = $bookingResponse[0]['id'];
    
    // ADD ROOM ITEMS
    foreach ($itemsData['rooms'] as $room) {
        if (!empty($room['roomId'])) {
            $supabase->addBookingItem($bookingId, [
                'item_type'        => 'room',
                'item_id'          => $room['roomId'],
                'item_name'        => $room['name'],
                'number_of_nights' => $room['nights'],
                'price_per_night'  => $room['pricePerNight'],
                'quantity'         => 1,
                'subtotal'         => $room['subtotal'],
            ]);
        }
    }
    
    // ADD COTTAGE ITEMS
    foreach ($itemsData['cottages'] as $cottage) {
        if (!empty($cottage['cottageId'])) {
            $supabase->addBookingItem($bookingId, [
                'item_type'        => 'cottage',
                'item_id'          => $cottage['cottageId'],
                'item_name'        => $cottage['name'],
                'number_of_nights' => $cottage['nights'],
                'price_per_night'  => $cottage['pricePerNight'],
                'quantity'         => 1,
                'subtotal'         => $cottage['subtotal'],
            ]);
        }
    }
    
    // ADD EQUIPMENT ITEMS
    foreach ($itemsData['equipment'] as $eq) {
        if (!empty($eq['equipmentId'])) {
            $supabase->addBookingItem($bookingId, [
                'item_type'       => 'equipment',
                'item_id'         => $eq['equipmentId'],
                'item_name'       => $eq['name'],
                'quantity'        => $eq['quantity'],
                'price_per_unit'  => $eq['pricePerUnit'],
                'subtotal'        => $eq['subtotal'],
            ]);
        }
    }
    
    // LOG ACTION
    $supabase->log('walkin_booking_created', [
        'target_type'  => 'walk_in_booking',
        'target_id'    => $bookingId,
        'target_label' => $request->guest_name . ' — ' . $transactionId . ' (' . $nights . ' night' . ($nights > 1 ? 's' : '') . ', ₱' . number_format($total, 2) . ')',
        'amount'       => $total,
    ]);
    
    return redirect("/admin/walkin/booking/$bookingId/payment")
        ->with('success', '✅ Booking created! ₱' . number_format($total, 2) . ' for ' . $nights . ' night(s)');
});

// PAYMENT PAGE
Route::get('/admin/walkin/booking/{id}/payment', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    $payments = $supabase->getWalkInPayments($booking['transaction_id']);
    
    return view('admin.walkin-booking-payment', compact('booking', 'payments', 'supabase'));
});

// RECORD PAYMENT
Route::post('/admin/walkin/booking/{id}/payment', function ($id, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $request->validate(['cash_received' => 'required|numeric|min:0.01']);
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    $cash = (float)$request->cash_received;
    $total = (float)$booking['total_amount'];
    $alreadyPaid = (float)($booking['paid_amount'] ?? 0);
    $balance = $total - $alreadyPaid;
    
    if ($cash <= 0) return back()->with('error', 'Invalid amount');
    if ($cash > $balance) $cash = $balance;
    
    $newPaid = $alreadyPaid + $cash;
    $newBalance = $total - $newPaid;
    $newStatus = ($newBalance <= 0) ? 'paid' : 'partial';
    
    // UPDATE BOOKING
    $supabase->updateWalkInBooking($id, [
        'number_of_nights' => $booking['number_of_nights'],
        'total_amount'     => $booking['total_amount'],
        'paid_amount'      => $newPaid,
        'balance_amount'   => $newBalance,
        'payment_status'   => $newStatus,
        'booking_status'   => $booking['booking_status'],
    ]);
    
    // RECORD PAYMENT
    $supabase->recordWalkInPayment([
        'transaction_id'   => $booking['transaction_id'],
        'transaction_type' => 'booking',
        'parent_id'        => $id,
        'guest_name'       => $booking['guest_name'],
        'amount_received'  => $cash,
        'payment_method'   => 'cash',
        'payment_type'     => $newStatus === 'paid' ? 'full' : 'partial',
    ]);
    
    $supabase->log('walkin_booking_payment', [
        'target_type'  => 'walk_in_booking',
        'target_id'    => $id,
        'target_label' => $booking['guest_name'] . ' — ₱' . number_format($cash, 2),
        'amount'       => $cash,
    ]);
    
    return back()->with('success', '✅ Payment recorded! ₱' . number_format($cash, 2));
});

// CHECK-IN
Route::post('/admin/walkin/booking/{id}/checkin', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    $supabase->updateWalkInBooking($id, [
        'number_of_nights' => $booking['number_of_nights'],
        'total_amount'     => $booking['total_amount'],
        'paid_amount'      => $booking['paid_amount'],
        'balance_amount'   => $booking['balance_amount'],
        'payment_status'   => $booking['payment_status'],
        'booking_status'   => 'checked_in',
    ]);
    
    $supabase->log('walkin_booking_checkin', [
        'target_type'  => 'walk_in_booking',
        'target_id'    => $id,
        'target_label' => $booking['guest_name'],
    ]);
    
    return back()->with('success', '✅ Guest checked in');
});

// CHECK-OUT
Route::post('/admin/walkin/booking/{id}/checkout', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    $supabase->updateWalkInBooking($id, [
        'number_of_nights' => $booking['number_of_nights'],
        'total_amount'     => $booking['total_amount'],
        'paid_amount'      => $booking['paid_amount'],
        'balance_amount'   => $booking['balance_amount'],
        'payment_status'   => $booking['payment_status'],
        'booking_status'   => 'checked_out',
    ]);
    
    $supabase->log('walkin_booking_checkout', [
        'target_type'  => 'walk_in_booking',
        'target_id'    => $id,
        'target_label' => $booking['guest_name'],
    ]);
    
    return back()->with('success', '✅ Guest checked out');
});

// VIEW ALL BOOKINGS
Route::get('/admin/walkin/bookings', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    // For now, get cottage bookings as placeholder
    $allBookings = $supabase->getCottageBookings() ?? [];
    $filter = $request->get('filter', 'upcoming');
    
    $bookings = collect($allBookings);
    $today = date('Y-m-d');
    
    if ($filter === 'upcoming') {
        $bookings = $bookings->where('booking_status', 'confirmed')
                              ->where('check_in', '>=', $today)
                              ->sortBy('check_in')
                              ->values();
    } elseif ($filter === 'checked_in') {
        $bookings = $bookings->where('booking_status', 'checked_in')->values();
    } elseif ($filter === 'unpaid') {
        $bookings = $bookings->where('payment_status', 'unpaid')->values();
    } else {
        $bookings = $bookings->sortByDesc('created_at')->values();
    }
    
    return view('admin.walkin-bookings-list', compact('bookings', 'filter'));
});

// RECEIPT
Route::get('/admin/walkin/booking/{id}/receipt', function ($id, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $booking = $supabase->getBookingWithItems($id);
    if (!$booking) return back()->with('error', 'Booking not found');
    
    return view('admin.walkin-booking-receipt', compact('booking'));
});




// REPLACE the /admin/walkin/pos route with this (uses direct HTTP calls):

Route::get('/admin/walkin/pos', function () {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_KEY');
    $headers = [
        'apikey' => $supabaseKey,
        'Authorization' => 'Bearer ' . $supabaseKey,
        'Content-Type' => 'application/json'
    ];
    
    // Fetch data
    try {
        $rooms = \Illuminate\Support\Facades\Http::withHeaders($headers)
            ->get("$supabaseUrl/rest/v1/rooms")
            ->json() ?? [];
    } catch (\Exception $e) {
        $rooms = [];
    }
    
    try {
        $cottages = \Illuminate\Support\Facades\Http::withHeaders($headers)
            ->get("$supabaseUrl/rest/v1/cottages")
            ->json() ?? [];
    } catch (\Exception $e) {
        $cottages = [];
    }
    
    try {
        $dayTourPackages = \Illuminate\Support\Facades\Http::withHeaders($headers)
            ->get("$supabaseUrl/rest/v1/day_tour_packages")
            ->json() ?? [];
    } catch (\Exception $e) {
        $dayTourPackages = [];
    }
    
    try {
        $equipmentTypes = \Illuminate\Support\Facades\Http::withHeaders($headers)
            ->get("$supabaseUrl/rest/v1/equipment_types")
            ->json() ?? [];
    } catch (\Exception $e) {
        $equipmentTypes = [];
    }
    
    return view('admin.walkin-pos-unified', compact(
        'rooms', 'cottages', 'dayTourPackages', 'equipmentTypes'
    ));
});




// ===== CREATE TRANSACTION (All types) =====
Route::post('/admin/walkin/create', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $request->validate([
        'guest_name'      => 'required|string|max:255',
        'guest_phone'     => 'required|string|max:20',
        'transaction_type' => 'required|in:day_tour,booking,equipment',
        'items_json'      => 'required|json',
    ]);
    
    $itemsData = json_decode($request->items_json, true);
    $type = $request->transaction_type;
    
    if (empty($itemsData['packages']) && empty($itemsData['rooms']) && empty($itemsData['equipment'])) {
        return back()->withErrors(['items' => 'Please add at least one item']);
    }
    
    // Calculate total
    $total = 0;
    foreach ($itemsData['packages'] ?? [] as $pkg) $total += (float)($pkg['subtotal'] ?? 0);
    foreach ($itemsData['rooms'] ?? [] as $room) $total += (float)($room['subtotal'] ?? 0);
    foreach ($itemsData['equipment'] ?? [] as $eq) $total += (float)($eq['subtotal'] ?? 0);
    
    // Create transaction
    $transactionId = $supabase->generateTransactionId('TRANS');
    
    $txData = [
        'transaction_id'    => $transactionId,
        'transaction_type'  => $type,
        'guest_name'        => $request->guest_name,
        'guest_phone'       => $request->guest_phone,
        'guest_email'       => $request->guest_email,
        'total_amount'      => $total,
        'paid_amount'       => 0,
        'balance_amount'    => $total,
        'payment_status'    => 'unpaid',
        'transaction_status' => 'pending',
        'notes'             => $request->notes,
    ];
    
    if ($type === 'booking') {
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;
        $startDate = new DateTime($checkIn);
        $endDate = new DateTime($checkOut);
        $nights = $endDate->diff($startDate)->days;
        if ($nights == 0) $nights = 1;
        
        $txData['check_in'] = $checkIn;
        $txData['check_out'] = $checkOut;
        $txData['number_of_nights'] = $nights;
    }
    
    $response = $supabase->createWalkInTransaction($txData);
    if (empty($response)) {
        return back()->withErrors(['error' => 'Failed to create transaction']);
    }
    
    $txId = $response[0]['id'];
    
    // Add items
    foreach ($itemsData['packages'] ?? [] as $pkg) {
        $supabase->addTransactionItem($transactionId, [
            'item_type'      => 'package',
            'item_id'        => $pkg['pkgId'],
            'item_name'      => $pkg['name'],
            'guest_count'    => $pkg['guestCount'],
            'price_per_unit' => $pkg['pricePerUnit'],
            'subtotal'       => $pkg['subtotal'],
        ]);
    }
    
    foreach ($itemsData['rooms'] ?? [] as $room) {
        if (!empty($room['roomId'])) {
            $supabase->addTransactionItem($transactionId, [
                'item_type'       => 'room',
                'item_id'         => $room['roomId'],
                'item_name'       => $room['name'],
                'number_of_nights' => $room['nights'],
                'price_per_unit'  => $room['pricePerNight'],
                'subtotal'        => $room['subtotal'],
            ]);
        }
    }
    
    foreach ($itemsData['equipment'] ?? [] as $eq) {
        if (!empty($eq['equipId'])) {
            $supabase->addTransactionItem($transactionId, [
                'item_type'      => 'equipment',
                'item_id'        => $eq['equipId'],
                'item_name'      => $eq['name'],
                'quantity'       => $eq['quantity'],
                'price_per_unit' => $eq['pricePerUnit'],
                'subtotal'       => $eq['subtotal'],
            ]);
        }
    }
    
    $supabase->log('transaction_created', [
        'target_type'  => 'walk_in_transaction',
        'target_id'    => $transactionId,
        'target_label' => $request->guest_name . ' — ' . $type . ' (₱' . number_format($total, 2) . ')',
        'amount'       => $total,
    ]);
    
    return redirect("/admin/walkin/payment/$transactionId")
        ->with('success', '✅ Transaction created! ₱' . number_format($total, 2));
});

// ===== PAYMENT PAGE =====
Route::get('/admin/walkin/payment/{transactionId}', function ($transactionId, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $transaction = $supabase->getTransactionWithItems($transactionId);
    if (!$transaction) return back()->with('error', 'Transaction not found');
    
    $payments = $supabase->getTransactionPayments($transaction['transaction_id']);
    
    return view('admin.walkin-payment', compact('transaction', 'payments'));
});

// ===== RECORD PAYMENT =====
Route::post('/admin/walkin/payment/{transactionId}', function ($transactionId, Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $request->validate(['cash_received' => 'required|numeric|min:0.01']);
    
    $transaction = $supabase->getTransactionWithItems($transactionId);
    if (!$transaction) return back()->with('error', 'Transaction not found');
    
    $cash = (float)$request->cash_received;
    $total = (float)$transaction['total_amount'];
    $alreadyPaid = (float)($transaction['paid_amount'] ?? 0);
    $balance = $total - $alreadyPaid;
    
    if ($cash <= 0) return back()->with('error', 'Invalid amount');
    if ($cash > $balance) $cash = $balance;
    
    $newPaid = $alreadyPaid + $cash;
    $newBalance = $total - $newPaid;
    $newStatus = ($newBalance <= 0) ? 'paid' : 'partial';
    
    // Update transaction
    $supabase->updateWalkInTransaction($transaction['transaction_id'], [
        'paid_amount'    => $newPaid,
        'balance_amount' => $newBalance,
        'payment_status' => $newStatus,
    ]);
    
    // Record payment
    $supabase->recordWalkInPayment([
        'transaction_id' => $transaction['transaction_id'],
        'amount_received' => $cash,
        'payment_method' => 'cash',
        'payment_type'   => $newStatus === 'paid' ? 'full' : 'partial',
        'staff_id'       => session('admin_id'),
        'staff_name'     => session('admin_name'),
    ]);
    
    $supabase->log('payment_recorded', [
        'target_type'  => 'walk_in_transaction',
        'target_id'    => $transaction['transaction_id'],
        'target_label' => $transaction['guest_name'] . ' — ₱' . number_format($cash, 2),
        'amount'       => $cash,
    ]);
    
    return back()->with('success', '✅ Payment recorded! ₱' . number_format($cash, 2));
});

// ===== CHECK-IN (for bookings) =====
Route::post('/admin/walkin/{transactionId}/checkin', function ($transactionId, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $transaction = $supabase->getTransactionWithItems($transactionId);
    if (!$transaction) return back()->with('error', 'Transaction not found');
    
    $supabase->updateWalkInTransaction($transaction['transaction_id'], [
        'transaction_status' => 'checked_in',
    ]);
    
    $supabase->log('checkin', [
        'target_type' => 'walk_in_transaction',
        'target_id'   => $transaction['transaction_id'],
        'target_label' => $transaction['guest_name'],
    ]);
    
    return back()->with('success', '✅ Guest checked in');
});

// ===== CHECK-OUT (for bookings) =====
Route::post('/admin/walkin/{transactionId}/checkout', function ($transactionId, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $transaction = $supabase->getTransactionWithItems($transactionId);
    if (!$transaction) return back()->with('error', 'Transaction not found');
    
    $supabase->updateWalkInTransaction($transaction['transaction_id'], [
        'transaction_status' => 'checked_out',
    ]);
    
    $supabase->log('checkout', [
        'target_type' => 'walk_in_transaction',
        'target_id'   => $transaction['transaction_id'],
        'target_label' => $transaction['guest_name'],
    ]);
    
    return back()->with('success', '✅ Guest checked out');
});

// ===== TRANSACTIONS LIST =====
Route::get('/admin/walkin/transactions', function (Request $request, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $type = $request->get('type');
    $status = $request->get('status');
    
    $transactions = $supabase->getAllTransactions($type, $status);
    
    return view('admin.walkin-transactions-list', compact('transactions', 'type', 'status'));
});

// ===== RECEIPT =====
Route::get('/admin/walkin/{transactionId}/receipt', function ($transactionId, SupabaseService $supabase) {
    if (!session('admin_logged_in')) return redirect('/admin/login');
    
    $transaction = $supabase->getTransactionWithItems($transactionId);
    if (!$transaction) return back()->with('error', 'Transaction not found');
    
    return view('admin.walkin-receipt', compact('transaction'));
});
