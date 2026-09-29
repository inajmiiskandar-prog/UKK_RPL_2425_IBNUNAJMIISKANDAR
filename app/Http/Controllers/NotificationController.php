<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function markAsRead(): RedirectResponse
    {
        session(['notifications_read_at' => now()->toIso8601String()]);

        return back();
    }
}