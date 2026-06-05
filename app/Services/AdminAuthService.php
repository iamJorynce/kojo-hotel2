<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AdminAuthService
{
    public function login($email, $password)
    {
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
        ])->get(env('SUPABASE_URL') . '/rest/v1/admins', [
            'email' => 'eq.' . $email,
        ])->json();

        $admin = $response[0] ?? null;

        if (!$admin) {
            return false;
        }

        if ($admin['password'] !== $password) {
            return false;
        }

        return $admin;
    }
}