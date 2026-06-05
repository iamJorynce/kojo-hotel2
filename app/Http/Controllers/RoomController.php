<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;

class RoomController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /*
    |--------------------------------------------------------------------------
    | HOME PAGE
    |--------------------------------------------------------------------------
    */
    public function home()
    {
        $rooms = $this->supabase->getRooms();

        return view('home', compact('rooms'));
    }

    /*
    |--------------------------------------------------------------------------
    | ROOMS PAGE
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $rooms = $this->supabase->getRooms();

        return view('rooms', compact('rooms'));
    }

    /*
    |--------------------------------------------------------------------------
    | SINGLE ROOM PAGE
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $rooms = $this->supabase->getRooms();
        $room = collect($rooms)->firstWhere('id', $id);

        return view('room-show', compact('room'));
    }

    /*
    |--------------------------------------------------------------------------
    | BOOKING FORM PAGE
    |--------------------------------------------------------------------------
    */
    public function bookForm($id)
    {
        $rooms = $this->supabase->getRooms();
        $room = collect($rooms)->firstWhere('id', $id);

        return view('book', compact('room'));
    }

    /*
    |--------------------------------------------------------------------------
    | BOOKING SUBMIT
    |--------------------------------------------------------------------------
    */
 public function storeBooking($id, Request $request)
{
    $rooms = $this->supabase->getRooms();

    $room = collect($rooms)->firstWhere('id', $id);

    $this->supabase->createBooking([
        'room_id' => $room['id'],   // UUID na ni
        'room_name' => $room['name'],
        'full_name' => $request->full_name ?? 'Walk-in Guest',
        'email' => $request->email,
        'check_in' => $request->check_in,
        'check_out' => $request->check_out,
        'status' => 'pending'
    ]);

    return redirect('/booking-success');
}
   /*
    |--------------------------------------------------------------------------
    | PENDING BOOKINGS
    |--------------------------------------------------------------------------
    */
public function pendingBookings()
{
    $supabase = new \App\Services\SupabaseService();

    $bookings = $supabase->getBookings();

    $bookings = array_map(function ($b) {

        $b['total_amount'] = $b['total_amount'] ?? 0;
        $b['downpayment_amount'] = $b['downpayment_amount'] ?? 0;

        // 🔥 BALANCE COMPUTATION (BEST PLACE)
        $b['balance_amount'] =
            $b['total_amount'] - $b['downpayment_amount'];

        return $b;

    }, $bookings);

    $bookings = array_filter($bookings, function ($b) {
        return ($b['status'] ?? 'pending') === 'pending';
    });

    return view('admin.bookings', compact('bookings'));
}

/*
    |--------------------------------------------------------------------------
    | CONFIRMED BOOKINGS
    |--------------------------------------------------------------------------
    */
    public function confirmedBookings()
{
    $supabase = new \App\Services\SupabaseService();

    $bookings = $supabase->getBookings();

    $bookings = array_map(function ($b) {

        $b['total_amount'] = $b['total_amount'] ?? 0;
        $b['downpayment_amount'] = $b['downpayment_amount'] ?? 0;

        $b['balance_amount'] =
            $b['total_amount'] - $b['downpayment_amount'];

        return $b;

    }, $bookings);

    $bookings = array_filter($bookings, function ($b) {
        return ($b['status'] ?? '') === 'confirmed';
    });

    return view('admin.confirmed', compact('bookings'));
}
/*
    |--------------------------------------------------------------------------
    | CANCELLED BOOKINGS
    |--------------------------------------------------------------------------
    */
 public function cancelledBookings()
{
    $supabase = new \App\Services\SupabaseService();

    $bookings = $supabase->getBookings();

    $bookings = array_filter($bookings, function ($b) {
        return ($b['status'] ?? '') === 'cancelled';
    });

    return view('admin.cancelled', compact('bookings'));
}
}