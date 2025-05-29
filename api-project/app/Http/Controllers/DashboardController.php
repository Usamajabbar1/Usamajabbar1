<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_admins' => User::role('admin')->count(),
            'total_accountants' => User::role('accountant')->count(),
            'total_viewers' => User::role('viewer')->count(),
        ]);
    }
}
