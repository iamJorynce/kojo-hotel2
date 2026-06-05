<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;

class TestController extends Controller
{
    public function test(SupabaseService $supabase)
    {
        $rooms = $supabase->get('rooms');
        return response()->json($rooms);
    }
}