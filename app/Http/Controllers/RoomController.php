<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $room  = collect($rooms)->firstWhere('id', $id);

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
        $room  = collect($rooms)->firstWhere('id', $id);

        return view('book', compact('room'));
    }

    /*
    |--------------------------------------------------------------------------
    | BOOKING SUBMIT
    | FIX: was using $room['id'] (integer) instead of $room['uuid_id']
    | which caused bookings to never match rooms in availability checks
    |--------------------------------------------------------------------------
    */
    public function storeBooking($id, Request $request)
    {
        $rooms = $this->supabase->getRooms();
        $room  = collect($rooms)->firstWhere('id', $id);

        if (!$room) {
            return redirect('/')->with('error', 'Room not found');
        }

        $this->supabase->createBooking([
            'room_uuid'  => $room['uuid_id'],   // FIX: was $room['id']
            'room_name'  => $room['name'],
            'full_name'  => $request->full_name ?? 'Walk-in Guest',
            'email'      => $request->email,
            'check_in'   => $request->check_in,
            'check_out'  => $request->check_out,
            'status'     => 'pending',
        ]);

        return redirect('/booking-success');
    }

    /*
    |--------------------------------------------------------------------------
    | PENDING BOOKINGS
    | FIX: was using $this->supabase instead of new instance (inconsistency)
    | FIX: balance was computed from downpayment_amount — changed to paid_amount
    |--------------------------------------------------------------------------
    */
    public function pendingBookings()
    {
        $bookings = $this->supabase->getBookings(); // FIX: use injected $this->supabase

        $bookings = array_map(function ($b) {

            $b['total_amount'] = (float) ($b['total_amount'] ?? 0);
            $b['paid_amount']  = (float) ($b['paid_amount']  ?? 0); // FIX: was downpayment_amount

            $b['balance_amount'] = $b['total_amount'] - $b['paid_amount'];

            return $b;

        }, $bookings);

        $bookings = array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'pending');

        return view('admin.bookings', compact('bookings'));
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRMED BOOKINGS
    | FIX: same fixes as pendingBookings — use $this->supabase and paid_amount
    |--------------------------------------------------------------------------
    */
    public function confirmedBookings()
    {
        $bookings = $this->supabase->getBookings(); // FIX: use injected $this->supabase

        $bookings = array_map(function ($b) {

            $b['total_amount'] = (float) ($b['total_amount'] ?? 0);
            $b['paid_amount']  = (float) ($b['paid_amount']  ?? 0); // FIX: was downpayment_amount

            $b['balance_amount'] = $b['total_amount'] - $b['paid_amount'];

            return $b;

        }, $bookings);

        $bookings = array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'confirmed');

        return view('admin.confirmed', compact('bookings'));
    }

    /*
    |--------------------------------------------------------------------------
    | CANCELLED BOOKINGS
    | FIX: use injected $this->supabase instead of new instance
    |--------------------------------------------------------------------------
    */
    public function cancelledBookings()
    {
        $bookings = $this->supabase->getBookings(); // FIX: use injected $this->supabase

        $bookings = array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'cancelled');

        return view('admin.cancelled', compact('bookings'));
    }
 /*
    |--------------------------------------------------------------------------
    | PrintReceipt
    |--------------------------------------------------------------------------
    */

    public function printReceipt($id)
    {
        $booking = $this->supabase->getBookingById($id);
        if (!$booking) {
            abort(404, 'Booking not found.');
        }
        $b = [
            'id'             => $booking['id'],
            'full_name'      => $booking['full_name'],
            'phone'          => $booking['phone']          ?? '—',
            'room_name'      => $booking['room_name']      ?? '—',
            'room_number'    => $booking['room_number']    ?? '—',
            'check_in'       => $booking['check_in'],
            'check_out'      => $booking['check_out'],
            'total_amount'   => (float) ($booking['total_amount']   ?? 0),
            'paid_amount'    => (float) ($booking['paid_amount']    ?? 0),
            'balance_amount' => (float) ($booking['balance_amount'] ?? 0),
            'payment_status' => $booking['payment_status'] ?? 'unpaid',
            'status'         => $booking['status']         ?? 'checked_out',
        ];
        $receiptNumber = 'OR-' . str_pad($id, 6, '0', STR_PAD_LEFT);
        $issuedAt      = now('Asia/Manila')->format('F d, Y h:i A');
        $pdf = Pdf::loadView('admin.receipt', compact('b', 'receiptNumber', 'issuedAt'))
                  ->setPaper('a5', 'portrait');
        return $pdf->stream("receipt-{$receiptNumber}.pdf");
    }
}