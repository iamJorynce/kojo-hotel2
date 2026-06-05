<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Supabase\CreateClient;

class SupabaseService
{
    private $url;
    private $key;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
        $this->key = env('SUPABASE_KEY');
    }
    /*
    |----------------------------------
    | HEADERS
    |----------------------------------
    */
private function headers()
{
    return [
        'apikey' => $this->key,
        'Authorization' => 'Bearer ' . $this->key,
        'Content-Type' => 'application/json'
    ];
}


 

    /*
    |----------------------------------
    | ROOMS
    |----------------------------------
    */
    public function getRooms()
{
    $response = Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/rooms?select=*');

    return $response->json();
}

    public function createRoom($data)
{
    $response = Http::withHeaders([
        'apikey' => $this->key,
        'Authorization' => 'Bearer ' . $this->key,
        'Content-Type' => 'application/json',
        'Prefer' => 'return=representation'
    ])->post($this->url . "/rest/v1/rooms", $data);

    return [
        'status' => $response->status(),
        'body' => $response->json()
    ];
}

public function updateRoom($id, $data)
{
    $response = Http::withHeaders([
        'apikey' => $this->key,
        'Authorization' => 'Bearer ' . $this->key,
        'Content-Type' => 'application/json',
        'Prefer' => 'return=representation'
    ])->patch($this->url . "/rest/v1/rooms?id=eq.$id", $data);

    return [
        'status' => $response->status(),
        'body' => $response->json(),
        'raw' => $response->body()
    ];
}

public function deleteRoom($id)
{
    return Http::withHeaders([
        'apikey' => $this->key,
        'Authorization' => 'Bearer ' . $this->key,
    ])->delete($this->url . "/rest/v1/rooms?id=eq.$id")
      ->json();
}

    /*
    |----------------------------------
    | BOOKINGS
    |----------------------------------
    */
    public function getBookings()
    {
        return Http::withHeaders($this->headers())
            ->get($this->url . '/rest/v1/bookings?select=*')
            ->json();
    }



public function createBooking($data)
{
    $response = Http::withHeaders([
        'apikey' => env('SUPABASE_KEY'),
        'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
        'Content-Type' => 'application/json',
        'Prefer' => 'return=representation'
    ])->post(env('SUPABASE_URL') . '/rest/v1/bookings', $data);

    return $response->json(); // 🔥 IMPORTANT
}

public function updateBooking($id, $data)
{
    return Http::withHeaders($this->headers())
        ->patch($this->url . "/rest/v1/bookings?id=eq.$id", $data)
        ->json();
}

    public function updateBookingStatus($id, $status)
    {
        return Http::withHeaders($this->headers())
            ->patch($this->url . '/rest/v1/bookings?id=eq.' . $id, [
                'status' => $status
            ])
            ->json();
    }

    public function updateBookingPayment($id, $status)
    {
        return Http::withHeaders($this->headers())
            ->patch($this->url . '/rest/v1/bookings?id=eq.' . $id, [
                'payment_status' => $status
            ])
            ->json();
    }

    /*
    |----------------------------------
    | LOGIN
    |----------------------------------
    */
    public function login($email, $password)
    {
        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
        ])->get($this->url . '/rest/v1/users', [
            'email' => 'eq.' . $email
        ])->json();

        $user = $response[0] ?? null;

        if (!$user) return false;

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }

    /*
    |----------------------------------
    | AVAILABILITY CHECK
    |----------------------------------
    */
    public function checkAvailability($roomUuid, $checkIn, $checkOut)
{
    $bookings = $this->getBookings();

    foreach ($bookings as $b) {

        if (($b['room_uuid'] ?? null) !== $roomUuid) {
            continue;
        }

        $existingStart = $b['check_in'];
        $existingEnd = $b['check_out'];

        // 🚨 OVERLAP CHECK (CRITICAL)
        if (
        $checkIn < $existingEnd &&
        $checkOut > $existingStart &&
        in_array($b['status'], ['confirmed', 'checked_in'])
        ) {
        return [
            'available' => false
        ];
        }
    }

    return [
        'available' => true
    ];
}
    /*
    |----------------------------------
    | ROOM STATUS ENGINE
    |----------------------------------
    */
    public function updateRoomStatus($roomUuid)
{
    $bookings = $this->getBookings();

    $latest = null;

    foreach ($bookings as $b) {

        if (($b['room_uuid'] ?? null) !== $roomUuid) {
            continue;
        }

        $latest = $b; // last booking wins
    }

    if (!$latest) {
        return $this->updateRoomByUuid($roomUuid, [
            'status' => 'available'
        ]);
    }

    $status = $latest['status'] ?? '';
    $payment = $latest['payment_status'] ?? '';

    $roomStatus = 'available';

    if ($status === 'checked_out') {
        $roomStatus = 'available';
    }
    elseif ($status === 'checked_in' || $payment === 'fully_paid') {
        $roomStatus = 'occupied';
    }
    elseif ($payment === 'downpayment_paid') {
        $roomStatus = 'reserved';
    }

    return $this->updateRoomByUuid($roomUuid, [
        'status' => $roomStatus
    ]);
}

    /*
    |----------------------------------
    | UPDATE ROOM BY UUID
    |----------------------------------
    */
    public function updateRoomByUuid($uuid, $data)
    {
        return Http::withHeaders($this->headers())
            ->patch($this->url . '/rest/v1/rooms?uuid_id=eq.' . $uuid, $data)
            ->json();
    }
/*
    |----------------------------------
    | GET ROOM CATEGORIES
    |----------------------------------
    */
public function getRoomCategories()
{
    $response = Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/room_categories?select=*');

    return $response->json();
}

/*
|--------------------------------------------------------------------------
| CORE AVAILABILITY FUNCTION (IMPORTANT)
|--------------------------------------------------------------------------
*/


public function isRoomAvailable($roomUuid, $checkIn, $checkOut)
{
    $bookings = collect($this->getBookings());

    return !$bookings->contains(function ($b) use ($roomUuid, $checkIn, $checkOut) {

        if (($b['room_uuid'] ?? null) !== $roomUuid) {
            return false;
        }

        // Only confirmed and checked_in can block
        if (!in_array($b['status'] ?? '', ['confirmed', 'checked_in'])) {
            return false;
        }

        return (
            $b['check_in'] < $checkOut &&
            $b['check_out'] > $checkIn
        );
    });
}


/*
|--------------------------------------------------------------------------
| helper
|--------------------------------------------------------------------------
*/
public function syncRoomStatus($roomUuid, $status)
{
    $this->updateRoom($roomUuid, [
        'status' => $status
    ]);
}

public function createRoomCategory($data)
{
    return $this->insert('room_categories', [
        'name' => $data['name'],
        'price' => $data['price'],
        'slug' => \Str::slug($data['name']) // 🔥 ADD THIS
    ]);
}

public function insert($table, $data)
{
    return Http::withHeaders([
        'apikey' => $this->key,
        'Authorization' => 'Bearer ' . $this->key,
        'Content-Type' => 'application/json',
        'Prefer' => 'return=representation'
    ])->post($this->url . "/rest/v1/" . $table, $data)->json();
}
public function deleteRoomCategory($id)
{
    return Http::withHeaders([
        'apikey' => $this->key,
        'Authorization' => 'Bearer ' . $this->key,
    ])->delete($this->url . "/rest/v1/room_categories?id=eq." . $id);
}

public function updateRoomCategory($id, $data)
{
    return Http::withHeaders([
        'apikey' => $this->key,
        'Authorization' => 'Bearer ' . $this->key,
        'Content-Type' => 'application/json',
        'Prefer' => 'return=representation'
    ])->patch($this->url . "/rest/v1/room_categories?id=eq.$id", [
        'name' => $data['name'],
        'price' => $data['price'],
        'description' => $data['description'],
    ])->json();
}
/*
|--------------------------------------------------------------------------
| SA CANCELLED BOOKINGS NI PARA DILI AMG ERROR
|--------------------------------------------------------------------------
*/

public function getBookingById($id)
{
    return collect($this->getBookings())
        ->firstWhere('id', $id);
}
/*
|--------------------------------------------------------------------------
| has clonflicts
|--------------------------------------------------------------------------
*/
public function hasConflict($bookings, $current)
{
    return collect($bookings)->contains(function ($b) use ($current) {

        if (($b['room_uuid'] ?? null) !== $current['room_uuid']) {
            return false;
        }

        if (!in_array($b['status'], ['confirmed', 'checked_in'])) {
            return false;
        }

        return (
            $b['check_in'] < $current['check_out'] &&
            $b['check_out'] > $current['check_in']
        );
    });
}

}