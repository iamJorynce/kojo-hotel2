<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupabaseService
{
    private $url;
    private $key;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
        $this->key = env('SUPABASE_KEY');
    }

    private function headers()
    {
        return [
            'apikey'        => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type'  => 'application/json',
        ];
    }

    // ROOMS
  
    public function createRoom($data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/rooms', $data)
            ->json();
    }

    public function updateRoom($id, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->patch($this->url . "/rest/v1/rooms?id=eq.$id", $data)
            ->json();
    }

    public function updateRoomByUuid($uuid, $data)
    {
        return Http::withHeaders($this->headers())
            ->patch($this->url . '/rest/v1/rooms?uuid_id=eq.' . $uuid, $data)
            ->json();
    }

    public function deleteRoom($id)
    {
        return Http::withHeaders($this->headers())
            ->delete($this->url . "/rest/v1/rooms?id=eq.$id")
            ->json();
    }

    // BOOKINGS
    public function getBookings()
    {
        return Http::withHeaders($this->headers())
            ->get($this->url . '/rest/v1/bookings?select=*')
            ->json();
    }

    public function createBooking($data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/bookings', $data)
            ->json();
    }

    public function updateBooking($id, $data)
    {
        return Http::withHeaders($this->headers())
            ->patch($this->url . "/rest/v1/bookings?id=eq.$id", $data)
            ->json();
    }

    public function updateBookingStatus($id, $status)
    {
        return $this->updateBooking($id, ['status' => $status]);
    }

    public function updateBookingPayment($id, $status)
    {
        return $this->updateBooking($id, ['payment_status' => $status]);
    }

    public function getBookingById($id)
    {
        return collect($this->getBookings())->firstWhere('id', $id);
    }

    // LOGIN
    public function login($email, $password)
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->url . '/rest/v1/users?email=eq.' . $email)
            ->json();

        $user = $response[0] ?? null;
        if (!$user) return false;
        if (!password_verify($password, $user['password'])) return false;
        return $user;
    }

    public function loginStaff($email, $password)
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->url . '/rest/v1/staff?email=eq.' . $email . '&is_active=eq.true')
            ->json();

        $user = $response[0] ?? null;
        if (!$user) return false;
        if (!password_verify($password, $user['password'])) return false;

        Http::withHeaders($this->headers())
            ->patch($this->url . "/rest/v1/staff?id=eq.{$user['id']}", [
                'last_login_at' => now()->toISOString(),
            ]);

        return $user;
    }

    // AVAILABILITY
    public function checkAvailability($roomUuid, $checkIn, $checkOut)
    {
        $bookings = $this->getBookings();
        foreach ($bookings as $b) {
            if (($b['room_uuid'] ?? null) !== $roomUuid) continue;
            if (!in_array($b['status'] ?? '', ['confirmed', 'checked_in'])) continue;
            if ($checkIn < $b['check_out'] && $checkOut > $b['check_in']) {
                return ['available' => false];
            }
        }
        return ['available' => true];
    }

    public function isRoomAvailable($roomUuid, $checkIn, $checkOut)
    {
        $bookings = collect($this->getBookings());
        return !$bookings->contains(function ($b) use ($roomUuid, $checkIn, $checkOut) {
            if (($b['room_uuid'] ?? null) !== $roomUuid) return false;
            if (!in_array($b['status'] ?? '', ['confirmed', 'checked_in'])) return false;
            return $b['check_in'] < $checkOut && $b['check_out'] > $checkIn;
        });
    }

    public function syncRoomStatus($roomUuid, $status)
    {
        $this->updateRoomByUuid($roomUuid, ['status' => $status]);
    }

    public function updateRoomStatus($roomUuid)
    {
        $bookings = collect($this->getBookings())
            ->filter(fn($b) => ($b['room_uuid'] ?? null) === $roomUuid)
            ->sortByDesc('check_out');

        $latest = $bookings->first();
        if (!$latest) {
            return $this->updateRoomByUuid($roomUuid, ['status' => 'available']);
        }

        $status = $latest['status'] ?? '';
        $payment = $latest['payment_status'] ?? '';

        if ($status === 'checked_out') {
            $roomStatus = 'available';
        } elseif ($status === 'checked_in' || $payment === 'paid') {
            $roomStatus = 'occupied';
        } elseif (in_array($payment, ['partial', 'downpayment_paid'])) {
            $roomStatus = 'reserved';
        } else {
            $roomStatus = 'available';
        }

        return $this->updateRoomByUuid($roomUuid, ['status' => $roomStatus]);
    }

    // ROOM CATEGORIES
    public function getRoomCategories()
    {
        return Http::withHeaders($this->headers())
            ->get($this->url . '/rest/v1/room_categories?select=*')
            ->json();
    }

    public function createRoomCategory($data)
    {
        return $this->insert('room_categories', [
            'name'        => $data['name'],
            'price'       => $data['price'],
            'description' => $data['description'] ?? '',
            'slug'        => Str::slug($data['name']),
        ]);
    }

    public function updateRoomCategory($id, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->patch($this->url . "/rest/v1/room_categories?id=eq.$id", [
                'name'        => $data['name'],
                'price'       => $data['price'],
                'description' => $data['description'] ?? '',
            ])
            ->json();
    }

    public function deleteRoomCategory($id)
    {
        return Http::withHeaders($this->headers())
            ->delete($this->url . "/rest/v1/room_categories?id=eq.$id");
    }

    // CONFLICT CHECK
    public function hasConflict($bookings, $current, $excludeId = null)
    {
        return collect($bookings)->contains(function ($b) use ($current, $excludeId) {
            if ($excludeId !== null && $b['id'] === $excludeId) return false;
            if (($b['room_uuid'] ?? null) !== ($current['room_uuid'] ?? null)) return false;
            if (!in_array($b['status'] ?? '', ['confirmed', 'checked_in'])) return false;
            return $b['check_in'] < $current['check_out'] && $b['check_out'] > $current['check_in'];
        });
    }

    // INSERT HELPER
    public function insert($table, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . "/rest/v1/$table", $data)->json();
    }

    // DAY TOUR PACKAGES
   

    public function createDayTourPackage($data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/day_tour_packages', $data)->json();
    }

    public function updateDayTourPackage($id, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->patch($this->url . "/rest/v1/day_tour_packages?id=eq.$id", $data)->json();
    }

    public function deleteDayTourPackage($id)
    {
        return Http::withHeaders($this->headers())
            ->delete($this->url . "/rest/v1/day_tour_packages?id=eq.$id")
            ->json();
    }

    // DAY TOURS
    public function getDayTours()
    {
        return Http::withHeaders($this->headers())
            ->get($this->url . '/rest/v1/day_tours?select=*&order=visit_date.desc,created_at.desc')
            ->json();
    }

    public function getDayTourById($id)
    {
        $result = Http::withHeaders($this->headers())
            ->get($this->url . "/rest/v1/day_tours?id=eq.$id&select=*")
            ->json();
        return $result[0] ?? null;
    }

    public function createDayTour($data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/day_tours', $data)->json();
    }

    public function updateDayTour($id, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->patch($this->url . "/rest/v1/day_tours?id=eq.$id", $data)->json();
    }

    // STAFF
    public function getStaff()
    {
        return Http::withHeaders($this->headers())
            ->get($this->url . '/rest/v1/staff?select=id,full_name,email,role,is_active,created_at,last_login_at&order=created_at.desc')
            ->json();
    }

    public function getStaffById($id)
    {
        $result = Http::withHeaders($this->headers())
            ->get($this->url . "/rest/v1/staff?id=eq.$id&select=*")
            ->json();
        return $result[0] ?? null;
    }

    public function createStaff($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/staff', $data)->json();
    }

    public function updateStaff($id, $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->patch($this->url . "/rest/v1/staff?id=eq.$id", $data)->json();
    }

    public function toggleStaffActive($id, $isActive)
    {
        return Http::withHeaders($this->headers())
            ->patch($this->url . "/rest/v1/staff?id=eq.$id", ['is_active' => $isActive])
            ->json();
    }

    public function deleteStaff($id)
    {
        return Http::withHeaders($this->headers())
            ->delete($this->url . "/rest/v1/staff?id=eq.$id")
            ->json();
    }

    // AUDIT LOG
    public function log($action, $data = [])
    {
        $staffId   = session('admin_id')   ?? null;
        $staffName = session('admin_name') ?? 'Unknown';
        $staffRole = session('admin_role') ?? 'unknown';

        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=minimal']))
            ->post($this->url . '/rest/v1/audit_logs', [
                'staff_id'     => $staffId,
                'staff_name'   => $staffName,
                'staff_role'   => $staffRole,
                'action'       => $action,
                'target_type'  => $data['target_type']  ?? null,
                'target_id'    => (string) ($data['target_id']   ?? ''),
                'target_label' => $data['target_label'] ?? null,
                'amount'       => $data['amount']        ?? 0,
                'payment_type' => $data['payment_type']  ?? null,
                'notes'        => $data['notes']         ?? null,
                'ip_address'   => request()->ip(),
            ])->json();
    }

    public function getAuditLogs($filters = [])
    {
        $query = '/rest/v1/audit_logs?select=*&order=created_at.desc';

        if (!empty($filters['staff_id'])) {
            $query .= '&staff_id=eq.' . $filters['staff_id'];
        }
        if (!empty($filters['action'])) {
            $query .= '&action=eq.' . $filters['action'];
        }
        if (!empty($filters['date'])) {
            $query .= '&created_at=gte.' . $filters['date'] . 'T00:00:00'
                    . '&created_at=lte.' . $filters['date'] . 'T23:59:59';
        }
        $query .= '&limit=500';

        return Http::withHeaders($this->headers())
            ->get($this->url . $query)
            ->json();
    }

    // PAYMENT RECORDS
    public function recordPayment($data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/payment_records', [
                'staff_id'       => session('admin_id')   ?? null,
                'staff_name'     => session('admin_name') ?? 'Unknown',
                'target_type'    => $data['target_type'],
                'target_id'      => (string) $data['target_id'],
                'guest_name'     => $data['guest_name']    ?? null,
                'room_info'      => $data['room_info']     ?? null,
                'amount_received'=> $data['amount_received'],
                'payment_type'   => $data['payment_type']  ?? null,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'total_amount'   => $data['total_amount']  ?? null,
                'balance_after'  => $data['balance_after'] ?? null,
            ])->json();
    }

    public function getPaymentRecords($filters = [])
    {
        $query = '/rest/v1/payment_records?select=*&order=received_at.desc';

        if (!empty($filters['staff_id'])) {
            $query .= '&staff_id=eq.' . $filters['staff_id'];
        }
        if (!empty($filters['target_type'])) {
            $query .= '&target_type=eq.' . $filters['target_type'];
        }
        if (!empty($filters['date'])) {
            $query .= '&received_at=gte.' . $filters['date'] . 'T00:00:00'
                    . '&received_at=lte.' . $filters['date'] . 'T23:59:59';
        }
        $query .= '&limit=500';

        return Http::withHeaders($this->headers())
            ->get($this->url . $query)
            ->json();
    }

    // EQUIPMENT TYPES


    public function getEquipmentTypeById($id)
    {
        $result = Http::withHeaders($this->headers())
            ->get($this->url . "/rest/v1/equipment_types?id=eq.$id&select=*")
            ->json();
        return $result[0] ?? null;
    }

    public function createEquipmentType($data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/equipment_types', [
                'name'                 => $data['name']              ?? null,
                'unit_price'           => $data['unit_price']        ?? 0,
                'quantity_available'   => $data['quantity_available'] ?? 0,
                'is_active'            => $data['is_active']         ?? true,
            ])->json();
    }

    public function updateEquipmentType($id, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->patch($this->url . "/rest/v1/equipment_types?id=eq.$id", [
                'name'                 => $data['name']              ?? null,
                'unit_price'           => $data['unit_price']        ?? null,
                'quantity_available'   => $data['quantity_available'] ?? null,
                'is_active'            => $data['is_active']         ?? null,
            ])->json();
    }

    public function deleteEquipmentType($id)
    {
        return Http::withHeaders($this->headers())
            ->delete($this->url . "/rest/v1/equipment_types?id=eq.$id")
            ->json();
    }

    // COTTAGES
  

    public function getCottageById($id)
    {
        $result = Http::withHeaders($this->headers())
            ->get($this->url . "/rest/v1/cottages?id=eq.$id&select=*")
            ->json();
        return $result[0] ?? null;
    }

    public function createCottage($data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/cottages', [
                'name'               => $data['name']               ?? null,
                'size_category'      => $data['size_category']      ?? 'small',
                'price_per_day'      => $data['price_per_day']      ?? 0,
                'quantity_available' => $data['quantity_available'] ?? 1,
                'description'        => $data['description']        ?? null,
                'is_active'          => $data['is_active']          ?? true,
            ])->json();
    }

    public function updateCottage($id, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->patch($this->url . "/rest/v1/cottages?id=eq.$id", [
                'name'               => $data['name']               ?? null,
                'size_category'      => $data['size_category']      ?? null,
                'price_per_day'      => $data['price_per_day']      ?? null,
                'quantity_available' => $data['quantity_available'] ?? null,
                'description'        => $data['description']        ?? null,
                'is_active'          => $data['is_active']          ?? null,
            ])->json();
    }

    public function deleteCottage($id)
    {
        return Http::withHeaders($this->headers())
            ->delete($this->url . "/rest/v1/cottages?id=eq.$id")
            ->json();
    }

    // EQUIPMENT RENTALS
    public function createEquipmentRental($data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/equipment_rentals', [
                'guest_name'      => $data['guest_name']      ?? null,
                'phone'           => $data['phone']           ?? null,
                'email'           => $data['email']           ?? null,
                'rental_date'     => $data['rental_date']     ?? null,
                'return_date'     => $data['return_date']     ?? null,
                'days'            => $data['days']            ?? 1,
                'total_amount'    => $data['total_amount']    ?? 0,
                'paid_amount'     => $data['paid_amount']     ?? 0,
                'balance_amount'  => $data['balance_amount']  ?? 0,
                'payment_status'  => $data['payment_status']  ?? 'unpaid',
                'status'          => $data['status']          ?? 'pending',
                'notes'           => $data['notes']           ?? null,
            ])->json();
    }

    public function getEquipmentRentals($filters = [])
    {
        $query = '/rest/v1/equipment_rentals?select=*&order=created_at.desc';

        if (!empty($filters['status'])) {
            $query .= '&status=eq.' . $filters['status'];
        }
        if (!empty($filters['date'])) {
            $query .= '&rental_date=eq.' . $filters['date'];
        }
        if (!empty($filters['guest_name'])) {
            $query .= '&guest_name=ilike.*' . urlencode($filters['guest_name']) . '*';
        }

        return Http::withHeaders($this->headers())
            ->get($this->url . $query)
            ->json();
    }

    public function getEquipmentRentalById($id)
    {
        $result = Http::withHeaders($this->headers())
            ->get($this->url . "/rest/v1/equipment_rentals?id=eq.$id&select=*")
            ->json();
        return $result[0] ?? null;
    }

    public function updateEquipmentRental($id, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->patch($this->url . "/rest/v1/equipment_rentals?id=eq.$id", [
                'guest_name'      => $data['guest_name']      ?? null,
                'phone'           => $data['phone']           ?? null,
                'email'           => $data['email']           ?? null,
                'rental_date'     => $data['rental_date']     ?? null,
                'return_date'     => $data['return_date']     ?? null,
                'days'            => $data['days']            ?? null,
                'total_amount'    => $data['total_amount']    ?? null,
                'paid_amount'     => $data['paid_amount']     ?? null,
                'balance_amount'  => $data['balance_amount']  ?? null,
                'payment_status'  => $data['payment_status']  ?? null,
                'status'          => $data['status']          ?? null,
                'notes'           => $data['notes']           ?? null,
            ])->json();
    }

    // RENTAL ITEMS
    public function addRentalItem($rentalId, $item)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/rental_items', [
                'rental_id'  => $rentalId,
                'item_type'  => $item['item_type']  ?? null,
                'item_id'    => $item['item_id']    ?? null,
                'item_name'  => $item['item_name']  ?? null,
                'quantity'   => $item['quantity']   ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'days'       => $item['days']       ?? 1,
                'subtotal'   => $item['subtotal']   ?? 0,
            ])->json();
    }

    public function getRentalItems($rentalId)
    {
        return Http::withHeaders($this->headers())
            ->get($this->url . "/rest/v1/rental_items?rental_id=eq.$rentalId&select=*")
            ->json();
    }

    public function deleteRentalItem($id)
    {
        return Http::withHeaders($this->headers())
            ->delete($this->url . "/rest/v1/rental_items?id=eq.$id")
            ->json();
    }

    // RENTAL RETURNS
    public function recordRentalReturn($rentalId, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->post($this->url . '/rest/v1/rental_returns', [
                'rental_id'          => $rentalId,
                'returned_date'      => $data['returned_date']      ?? date('Y-m-d'),
                'returned_time'      => $data['returned_time']      ?? null,
                'condition'          => $data['condition']          ?? 'good',
                'damage_description' => $data['damage_description'] ?? null,
                'damage_amount'      => $data['damage_amount']      ?? 0,
                'notes'              => $data['notes']              ?? null,
                'returned_by'        => $data['returned_by']        ?? null,
                'inspected_by'       => $data['inspected_by']       ?? session('admin_name'),
            ])->json();
    }

    public function getRentalReturn($rentalId)
    {
        $result = Http::withHeaders($this->headers())
            ->get($this->url . "/rest/v1/rental_returns?rental_id=eq.$rentalId&select=*&order=created_at.desc&limit=1")
            ->json();
        return $result[0] ?? null;
    }

    public function updateRentalReturn($id, $data)
    {
        return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
            ->patch($this->url . "/rest/v1/rental_returns?id=eq.$id", [
                'condition'          => $data['condition']          ?? null,
                'damage_description' => $data['damage_description'] ?? null,
                'damage_amount'      => $data['damage_amount']      ?? null,
                'notes'              => $data['notes']              ?? null,
                'inspected_by'       => $data['inspected_by']       ?? session('admin_name'),
            ])->json();
    }

    public function checkEquipmentAvailability($equipmentId, $rentalDate, $returnDate)
    {
        $rentals = Http::withHeaders($this->headers())
            ->get($this->url . "/rest/v1/equipment_rentals?select=id&status=neq.cancelled&rental_date=lte.$returnDate&return_date=gte.$rentalDate")
            ->json();

        if (empty($rentals)) {
            return ['available' => true];
        }

        $rentalIds = array_column($rentals, 'id');
        $query = '/rest/v1/rental_items?select=*&item_id=eq.' . $equipmentId;

        $items = Http::withHeaders($this->headers())
            ->get($this->url . $query)
            ->json();

        $totalRented = array_sum(array_column($items, 'quantity'));
        $equipment = $this->getEquipmentTypeById($equipmentId);
        $available = ($equipment['quantity_available'] ?? 0) - $totalRented;

        return [
            'available'       => $available > 0,
            'quantity_booked' => $totalRented,
            'quantity_left'   => max(0, $available),
        ];
    }

    /*
|==========================================================================
| ADD THESE METHODS TO SupabaseService.php
| Equipment Inventory Management
|==========================================================================
*/

// Deduct equipment when rented
public function deductEquipmentInventory($equipmentId, $quantity)
{
    $equipment = $this->getEquipmentTypeById($equipmentId);
    if (!$equipment) return false;
    
    $newQty = max(0, ($equipment['quantity_available'] ?? 0) - $quantity);
    
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->patch($this->url . "/rest/v1/equipment_types?id=eq.$equipmentId", [
            'quantity_available' => $newQty
        ])->json();
}

// Restore equipment when rental cancelled
public function restoreEquipmentInventory($equipmentId, $quantity)
{
    $equipment = $this->getEquipmentTypeById($equipmentId);
    if (!$equipment) return false;
    
    $newQty = ($equipment['quantity_available'] ?? 0) + $quantity;
    
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->patch($this->url . "/rest/v1/equipment_types?id=eq.$equipmentId", [
            'quantity_available' => $newQty
        ])->json();
}

// Payment Submission methods
public function createPaymentSubmission($data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->post($this->url . '/rest/v1/payment_submissions', [
            'staff_id'        => $data['staff_id']        ?? session('admin_id'),
            'staff_name'      => $data['staff_name']      ?? session('admin_name'),
            'submission_date' => $data['submission_date'] ?? date('Y-m-d'),
            'total_cash'      => $data['total_cash']      ?? 0,
            'payment_count'   => $data['payment_count']   ?? 0,
            'status'          => 'pending',
            'notes'           => $data['notes']           ?? null,
        ])->json();
}

public function getPaymentSubmissions($filters = [])
{
    $query = '/rest/v1/payment_submissions?select=*&order=created_at.desc';
    
    if (!empty($filters['status'])) {
        $query .= '&status=eq.' . $filters['status'];
    }
    if (!empty($filters['date'])) {
        $query .= '&submission_date=eq.' . $filters['date'];
    }
    if (!empty($filters['staff_id'])) {
        $query .= '&staff_id=eq.' . $filters['staff_id'];
    }
    
    $query .= '&limit=500';
    
    return Http::withHeaders($this->headers())
        ->get($this->url . $query)
        ->json();
}

public function getPaymentSubmissionById($id)
{
    $result = Http::withHeaders($this->headers())
        ->get($this->url . "/rest/v1/payment_submissions?id=eq.$id&select=*")
        ->json();
    return $result[0] ?? null;
}

public function updatePaymentSubmission($id, $data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->patch($this->url . "/rest/v1/payment_submissions?id=eq.$id", [
            'status'      => $data['status']      ?? null,
            'admin_id'    => $data['admin_id']    ?? session('admin_id'),
            'admin_name'  => $data['admin_name']  ?? session('admin_name'),
            'approved_at' => $data['approved_at'] ?? ($data['status'] === 'approved' ? now()->toISOString() : null),
            'notes'       => $data['notes']       ?? null,
        ])->json();
}

public function addSubmissionItem($submissionId, $item)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->post($this->url . '/rest/v1/payment_submission_items', [
            'submission_id'   => $submissionId,
            'payment_record_id' => $item['payment_record_id'] ?? null,
            'target_type'     => $item['target_type']     ?? null,
            'target_id'       => $item['target_id']       ?? null,
            'guest_name'      => $item['guest_name']      ?? null,
            'amount'          => $item['amount']          ?? 0,
        ])->json();
}

public function getSubmissionItems($submissionId)
{
    return Http::withHeaders($this->headers())
        ->get($this->url . "/rest/v1/payment_submission_items?submission_id=eq.$submissionId&select=*")
        ->json();
}




// COTTAGE BOOKINGS
public function createCottageBooking($data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->post($this->url . '/rest/v1/cottage_bookings', [
            'cottage_id'       => $data['cottage_id'],
            'guest_name'       => $data['guest_name'],
            'guest_email'      => $data['guest_email'] ?? null,
            'guest_phone'      => $data['guest_phone'],
            'check_in'         => $data['check_in'],
            'check_out'        => $data['check_out'],
            'number_of_nights' => $data['number_of_nights'],
            'price_per_night'  => $data['price_per_night'],
            'total_amount'     => $data['total_amount'],
            'paid_amount'      => $data['paid_amount'] ?? 0,
            'balance_amount'   => $data['balance_amount'],
            'payment_status'   => $data['payment_status'] ?? 'unpaid',
            'booking_status'   => $data['booking_status'] ?? 'confirmed',
            'notes'            => $data['notes'] ?? null,
        ])->json();
}

// COTTAGE BOOKINGS


public function getCottageBookings($filters = [])
{
    $query = '/rest/v1/cottage_bookings?select=*&order=check_in.desc';

    if (!empty($filters['cottage_id'])) {
        $query .= '&cottage_id=eq.' . $filters['cottage_id'];
    }
    if (!empty($filters['status'])) {
        $query .= '&booking_status=eq.' . $filters['status'];
    }
    if (!empty($filters['payment_status'])) {
        $query .= '&payment_status=eq.' . $filters['payment_status'];
    }

    return Http::withHeaders($this->headers())
        ->get($this->url . $query)
        ->json();
}

public function getCottageBookingById($id)
{
    $result = Http::withHeaders($this->headers())
        ->get($this->url . "/rest/v1/cottage_bookings?id=eq.$id&select=*")
        ->json();
    return $result[0] ?? null;
}

// ✅ COTTAGE AVAILABILITY - RENAMED to avoid conflict with equipment system
public function isCottageAvailableForDates($cottageId, $checkIn, $checkOut, $excludeBookingId = null)
{
    $bookings = collect($this->getCottageBookings(['cottage_id' => $cottageId]))
        ->filter(function ($b) use ($excludeBookingId) {
            if ($excludeBookingId && $b['id'] == $excludeBookingId) return false;
            return in_array($b['booking_status'] ?? '', ['confirmed', 'checked_in']);
        });

    foreach ($bookings as $b) {
        if ($checkIn < $b['check_out'] && $checkOut > $b['check_in']) {
            return false;
        }
    }

    return true;
}



public function updateCottageBooking($id, $data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->patch($this->url . "/rest/v1/cottage_bookings?id=eq.$id", [
            'guest_name'       => $data['guest_name'] ?? null,
            'guest_email'      => $data['guest_email'] ?? null,
            'guest_phone'      => $data['guest_phone'] ?? null,
            'check_in'         => $data['check_in'] ?? null,
            'check_out'        => $data['check_out'] ?? null,
            'number_of_nights' => $data['number_of_nights'] ?? null,
            'price_per_night'  => $data['price_per_night'] ?? null,
            'total_amount'     => $data['total_amount'] ?? null,
            'paid_amount'      => $data['paid_amount'] ?? null,
            'balance_amount'   => $data['balance_amount'] ?? null,
            'payment_status'   => $data['payment_status'] ?? null,
            'booking_status'   => $data['booking_status'] ?? null,
            'notes'            => $data['notes'] ?? null,
        ])->json();
}

// GET ALL COTTAGES WITH AVAILABILITY STATUS FOR GIVEN DATES
public function getCottagesWithAvailability($checkIn, $checkOut)
{
    $allCottages = $this->getCottages();
    $allBookings = collect($this->getCottageBookings());

    $result = [];
    foreach ($allCottages as $cottage) {
        // Check if this cottage has any conflicting booking
        $isBooked = $allBookings->contains(function ($b) use ($cottage, $checkIn, $checkOut) {
            if ($b['cottage_id'] != $cottage['id']) return false;
            if (!in_array($b['booking_status'] ?? '', ['confirmed', 'checked_in'])) return false;
            return $b['check_in'] < $checkOut && $b['check_out'] > $checkIn;
        });

        $cottage['is_available'] = !$isBooked;
        $cottage['status_label'] = $isBooked ? 'Booked' : 'Available';

        // Get cottage images
        $cottage['images'] = $this->getCottageImages($cottage['id']);
        $cottage['primary_image'] = collect($cottage['images'])->firstWhere('is_primary', true)
            ?? ($cottage['images'][0] ?? null);

        $result[] = $cottage;
    }

    return $result;
}

// CHECK COTTAGE AVAILABILITY


// CHECK IF SPECIFIC COTTAGE IS AVAILABLE FOR DATES
public function isCottageBookable($cottageId, $checkIn, $checkOut, $excludeId = null)
{
    $bookings = collect($this->getCottageBookings(['cottage_id' => $cottageId]))
        ->filter(function ($b) use ($excludeId) {
            if ($excludeId && $b['id'] == $excludeId) return false;
            return in_array($b['booking_status'] ?? '', ['confirmed', 'checked_in']);
        });

    foreach ($bookings as $b) {
        // Overlap check: b.check_in < checkOut AND b.check_out > checkIn
        if ($b['check_in'] < $checkOut && $b['check_out'] > $checkIn) {
            return false; // CONFLICT - NOT AVAILABLE
        }
    }

    return true; // AVAILABLE
}

public function isCottageAvailable($cottageId, $checkIn, $checkOut, $excludeBookingId = null)
{
    $bookings = collect($this->getCottageBookings(['cottage_id' => $cottageId]))
        ->filter(function ($b) use ($excludeBookingId) {
            if ($excludeBookingId && $b['id'] == $excludeBookingId) return false;
            return in_array($b['booking_status'] ?? '', ['confirmed', 'checked_in']);
        });

    foreach ($bookings as $b) {
        if ($checkIn < $b['check_out'] && $checkOut > $b['check_in']) {
            return false;
        }
    }

    return true;
}

// GET AVAILABLE COTTAGES FOR DATE RANGE
public function getAvailableCottages($checkIn, $checkOut)
{
    $allCottages = collect($this->getCottages());
    $bookings = collect($this->getCottageBookings());

    $available = [];
    foreach ($allCottages as $cottage) {
        $isBooked = $bookings->contains(function ($b) use ($cottage, $checkIn, $checkOut) {
            if ($b['cottage_id'] != $cottage['id']) return false;
            if (!in_array($b['booking_status'] ?? '', ['confirmed', 'checked_in'])) return false;
            return $checkIn < $b['check_out'] && $checkOut > $b['check_in'];
        });

        if (!$isBooked) {
            $cottage['available'] = true;
            $available[] = $cottage;
        }
    }

    return $available;
}

// COTTAGE BOOKING PAYMENTS
public function recordCottagePayment($bookingId, $data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->post($this->url . '/rest/v1/cottage_booking_payments', [
            'booking_id'       => $bookingId,
            'staff_id'         => $data['staff_id'] ?? session('admin_id'),
            'staff_name'       => $data['staff_name'] ?? session('admin_name'),
            'amount_received'  => $data['amount_received'],
            'payment_method'   => $data['payment_method'] ?? 'cash',
            'payment_type'     => $data['payment_type'] ?? 'full',
            'notes'            => $data['notes'] ?? null,
        ])->json();
}


public function getCottageBookingPayments($bookingId)
{
    return Http::withHeaders($this->headers())
        ->get($this->url . "/rest/v1/cottage_booking_payments?booking_id=eq.$bookingId&select=*&order=received_at.desc")
        ->json();
}



// COTTAGE IMAGES
public function addCottageImage($cottageId, $imageUrl, $description = null, $isPrimary = false)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->post($this->url . '/rest/v1/cottage_images', [
            'cottage_id'  => $cottageId,
            'image_url'   => $imageUrl,
            'description' => $description,
            'is_primary'  => $isPrimary,
        ])->json();
}


public function deleteCottageImage($id)
{
    return Http::withHeaders($this->headers())
        ->delete($this->url . "/rest/v1/cottage_images?id=eq.$id")
        ->json();
}

// COTTAGE PAYMENT
public function payCottageBooking($id, $amount, $staffName = null)
{
    $booking = $this->getCottageBookingById($id);
    if (!$booking) return false;

    $newPaid = (float)($booking['paid_amount'] ?? 0) + (float)$amount;
    $newBalance = (float)$booking['total_amount'] - $newPaid;
    $newStatus = $newBalance <= 0 ? 'paid' : 'unpaid';

    return $this->updateCottageBooking($id, [
        'paid_amount'    => $newPaid,
        'balance_amount' => max(0, $newBalance),
        'payment_status' => $newStatus,
    ]);
}



// ========================================================================
// WALK-IN DAY TOURS (MULTI-PACKAGE)
// ========================================================================

public function createWalkInDayTour($data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->post($this->url . '/rest/v1/walk_in_day_tours', [
            'transaction_id' => $data['transaction_id'],
            'guest_name'     => $data['guest_name'],
            'guest_phone'    => $data['guest_phone'],
            'guest_email'    => $data['guest_email'] ?? null,
            'total_guests'   => $data['total_guests'] ?? 0,
            'total_amount'   => $data['total_amount'],
            'paid_amount'    => $data['paid_amount'] ?? 0,
            'balance_amount' => $data['balance_amount'],
            'payment_status' => $data['payment_status'] ?? 'unpaid',
            'notes'          => $data['notes'] ?? null,
        ])->json();
}

public function addDayTourItem($tourId, $item)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->post($this->url . '/rest/v1/walk_in_day_tour_items', [
            'tour_id'        => $tourId,
            'item_type'      => $item['item_type'],
            'item_id'        => $item['item_id'],
            'item_name'      => $item['item_name'],
            'guest_count'    => $item['guest_count'] ?? 1,
            'price_per_unit' => $item['price_per_unit'],
            'quantity'       => $item['quantity'] ?? 1,
            'subtotal'       => $item['subtotal'],
            'notes'          => $item['notes'] ?? null,
        ])->json();
}

public function getDayTourWithItems($tourId)
{
    $tour = Http::withHeaders($this->headers())
        ->get($this->url . "/rest/v1/walk_in_day_tours?id=eq.$tourId&select=*")
        ->json();

    if (empty($tour)) return null;

    $tour = $tour[0];
    $items = Http::withHeaders($this->headers())
        ->get($this->url . "/rest/v1/walk_in_day_tour_items?tour_id=eq.$tourId&select=*")
        ->json();

    $tour['items'] = $items ?? [];
    return $tour;
}

public function updateWalkInDayTour($tourId, $data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->patch($this->url . "/rest/v1/walk_in_day_tours?id=eq.$tourId", [
            'total_guests'   => $data['total_guests'] ?? null,
            'total_amount'   => $data['total_amount'] ?? null,
            'paid_amount'    => $data['paid_amount'] ?? null,
            'balance_amount' => $data['balance_amount'] ?? null,
            'payment_status' => $data['payment_status'] ?? null,
            'notes'          => $data['notes'] ?? null,
        ])->json();
}

// ========================================================================
// WALK-IN BOOKINGS (MULTI-ITEM)
// ========================================================================

public function createWalkInBooking($data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->post($this->url . '/rest/v1/walk_in_bookings', [
            'transaction_id' => $data['transaction_id'],
            'guest_name'     => $data['guest_name'],
            'guest_phone'    => $data['guest_phone'],
            'guest_email'    => $data['guest_email'] ?? null,
            'check_in'       => $data['check_in'] ?? null,
            'check_out'      => $data['check_out'] ?? null,
            'number_of_nights' => $data['number_of_nights'] ?? null,
            'total_amount'   => $data['total_amount'],
            'paid_amount'    => $data['paid_amount'] ?? 0,
            'balance_amount' => $data['balance_amount'],
            'payment_status' => $data['payment_status'] ?? 'unpaid',
            'booking_status' => $data['booking_status'] ?? 'confirmed',
            'notes'          => $data['notes'] ?? null,
        ])->json();
}

public function addBookingItem($bookingId, $item)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->post($this->url . '/rest/v1/walk_in_booking_items', [
            'booking_id'      => $bookingId,
            'item_type'       => $item['item_type'],
            'item_id'         => $item['item_id'],
            'item_name'       => $item['item_name'],
            'number_of_nights'=> $item['number_of_nights'] ?? 1,
            'price_per_night' => $item['price_per_night'] ?? null,
            'quantity'        => $item['quantity'] ?? 1,
            'price_per_unit'  => $item['price_per_unit'] ?? null,
            'subtotal'        => $item['subtotal'],
            'notes'           => $item['notes'] ?? null,
        ])->json();
}

public function getBookingWithItems($bookingId)
{
    $booking = Http::withHeaders($this->headers())
        ->get($this->url . "/rest/v1/walk_in_bookings?id=eq.$bookingId&select=*")
        ->json();

    if (empty($booking)) return null;

    $booking = $booking[0];
    $items = Http::withHeaders($this->headers())
        ->get($this->url . "/rest/v1/walk_in_booking_items?booking_id=eq.$bookingId&select=*")
        ->json();

    $booking['items'] = $items ?? [];
    return $booking;
}

public function updateWalkInBooking($bookingId, $data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->patch($this->url . "/rest/v1/walk_in_bookings?id=eq.$bookingId", [
            'number_of_nights' => $data['number_of_nights'] ?? null,
            'total_amount'     => $data['total_amount'] ?? null,
            'paid_amount'      => $data['paid_amount'] ?? null,
            'balance_amount'   => $data['balance_amount'] ?? null,
            'payment_status'   => $data['payment_status'] ?? null,
            'booking_status'   => $data['booking_status'] ?? null,
            'notes'            => $data['notes'] ?? null,
        ])->json();
}

// ========================================================================
// PAYMENT RECORDING
// ========================================================================


public function getWalkInPayments($transactionId)
{
    return Http::withHeaders($this->headers())
        ->get($this->url . "/rest/v1/walk_in_payments?transaction_id=eq.$transactionId&select=*&order=received_at.desc")
        ->json();
}

// ========================================================================
// AVAILABILITY CHECKING FOR MULTI-ITEM SYSTEM
// ========================================================================

public function getAvailableRoomsForDates($checkIn, $checkOut)
{
    $allRooms = collect($this->getRooms());
    $bookings = collect($this->getBookings());

    $available = [];
    foreach ($allRooms as $room) {
        $isBooked = $bookings->contains(function ($b) use ($room, $checkIn, $checkOut) {
            if ($b['room_id'] != $room['id']) return false;
            return $checkIn < $b['check_out'] && $checkOut > $b['check_in'];
        });

        if (!$isBooked) {
            $available[] = $room;
        }
    }

    return $available;
}

public function getAvailableCottagesForDates($checkIn, $checkOut)
{
    $allCottages = collect($this->getCottages());
    $bookings = collect($this->getCottageBookings());

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

    return $available;
}

/**
 * Generate unique transaction ID
 */
public function generateTransactionId($prefix = 'TRANS')
{
    $date = date('Ymd');
    $random = strtoupper(substr(md5(time() . rand()), 0, 6));
    return "$prefix-$date-$random";
}

/**
 * Create a new walk-in transaction (day tour, booking, or equipment)
 */
public function createWalkInTransaction($data)
{
    return Http::withHeaders($this->headers())
        ->post($this->url . '/rest/v1/walk_in_transactions', $data)
        ->json();
}

/**
 * Update walk-in transaction
 */
public function updateWalkInTransaction($transactionId, $data)
{
    return Http::withHeaders(array_merge($this->headers(), ['Prefer' => 'return=representation']))
        ->patch($this->url . '/rest/v1/walk_in_transactions?transaction_id=eq.' . $transactionId, $data)
        ->json();
}

/**
 * Get transaction with all items
 */
public function getTransactionWithItems($transactionId)
{
    $transaction = Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/walk_in_transactions?transaction_id=eq.' . $transactionId)
        ->json();
    
    if (empty($transaction)) return null;
    
    $tx = $transaction[0];
    $items = Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/walk_in_transaction_items?transaction_id=eq.' . $transactionId)
        ->json();
    
    $tx['items'] = $items ?? [];
    return $tx;
}

/**
 * Add item to transaction
 */
public function addTransactionItem($transactionId, $item)
{
    return Http::withHeaders($this->headers())
        ->post($this->url . '/rest/v1/walk_in_transaction_items', array_merge(['transaction_id' => $transactionId], $item))
        ->json();
}

/**
 * Record payment for transaction
 */
public function recordWalkInPayment($data)
{
    return Http::withHeaders($this->headers())
        ->post($this->url . '/rest/v1/walk_in_payments', $data)
        ->json();
}

/**
 * Get all payments for transaction
 */
public function getTransactionPayments($transactionId)
{
    return Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/walk_in_payments?transaction_id=eq.' . $transactionId . '&order=received_at.desc')
        ->json();
}

/**
 * Get all transactions (with filters)
 */
public function getAllTransactions($type = null, $paymentStatus = null, $limit = 100)
{
    $query = '/rest/v1/walk_in_transactions?order=created_at.desc&limit=' . $limit;
    
    if ($type) {
        $query .= '&transaction_type=eq.' . $type;
    }
    
    if ($paymentStatus) {
        $query .= '&payment_status=eq.' . $paymentStatus;
    }
    
    return Http::withHeaders($this->headers())
        ->get($this->url . $query)
        ->json();
}

/**
 * Get transactions by date range
 */
public function getTransactionsByDateRange($startDate, $endDate, $type = null)
{
    $query = '/rest/v1/walk_in_transactions?created_at=gte.' . $startDate . '&created_at=lte.' . $endDate . '&order=created_at.desc';
    
    if ($type) {
        $query .= '&transaction_type=eq.' . $type;
    }
    
    return Http::withHeaders($this->headers())
        ->get($this->url . $query)
        ->json();
}

/**
 * Check room/cottage availability for dates
 */
public function checkAvailabilityForDates($itemId, $itemType, $checkIn, $checkOut)
{
    // Get all bookings that overlap with these dates
    $transactions = Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/walk_in_transactions?transaction_type=eq.booking&transaction_status=neq.cancelled')
        ->json();
    
    if (empty($transactions)) return true;
    
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    
    foreach ($transactions as $tx) {
        if (empty($tx['check_in']) || empty($tx['check_out'])) continue;
        
        $txCheckIn = new DateTime($tx['check_in']);
        $txCheckOut = new DateTime($tx['check_out']);
        
        // Check for overlap
        if ($checkInDate < $txCheckOut && $checkOutDate > $txCheckIn) {
            // Get items to see if this item_id is booked
            $items = Http::withHeaders($this->headers())
                ->get($this->url . '/rest/v1/walk_in_transaction_items?transaction_id=eq.' . $tx['transaction_id'] . '&item_id=eq.' . $itemId)
                ->json();
            
            if (!empty($items)) {
                return false; // Not available
            }
        }
    }
    
    return true; // Available
}

/**
 * Get available rooms/cottages for date range
 */
public function getAvailableItemsForDates($itemType, $checkIn, $checkOut)
{
    // Get all items of this type
    $allItems = ($itemType === 'room') ? $this->getRooms() : $this->getCottages();
    
    if (empty($allItems)) return [];
    
    $available = [];
    
    foreach ($allItems as $item) {
        if ($this->checkAvailabilityForDates($item['id'], $itemType, $checkIn, $checkOut)) {
            $available[] = $item;
        }
    }
    
    return $available;
}

/**
 * Get daily transaction summary
 */
public function getDailyTransactionSummary($date)
{
    $transactions = Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/walk_in_transactions?created_at=gte.' . $date . ' 00:00:00&created_at=lt.' . $date . ' 23:59:59')
        ->json();
    
    if (empty($transactions)) {
        return [
            'total_transactions' => 0,
            'total_revenue' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'by_type' => []
        ];
    }
    
    $summary = [
        'total_transactions' => count($transactions),
        'total_revenue' => array_sum(array_column($transactions, 'total_amount')),
        'paid' => 0,
        'unpaid' => 0,
        'partial' => 0,
        'by_type' => []
    ];
    
    foreach ($transactions as $tx) {
        // Count by payment status
        $status = $tx['payment_status'];
        if ($status === 'paid') $summary['paid']++;
        elseif ($status === 'unpaid') $summary['unpaid']++;
        else $summary['partial']++;
        
        // Count by type
        $type = $tx['transaction_type'];
        if (!isset($summary['by_type'][$type])) {
            $summary['by_type'][$type] = ['count' => 0, 'revenue' => 0];
        }
        $summary['by_type'][$type]['count']++;
        $summary['by_type'][$type]['revenue'] += $tx['total_amount'];
    }
    
    return $summary;
}

/**
 * Get payment history for transaction
 */
public function getPaymentHistory($transactionId)
{
    return Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/walk_in_payments?transaction_id=eq.' . $transactionId . '&order=received_at.desc')
        ->json();
}

/**
 * Get available day tour packages
 */
public function getDayTourPackages()
{
    return Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/day_tour_packages')
        ->json();
}

/**
 * Get rooms
 */
public function getRooms()
{
    return Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/rooms')
        ->json();
}

/**
 * Get cottages
 */
public function getCottages()
{
    return Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/cottages')
        ->json();
}

/**
 * Get equipment types
 */
public function getEquipmentTypes()
{
    return Http::withHeaders($this->headers())
        ->get($this->url . '/rest/v1/equipment_types')
        ->json();
}






}
