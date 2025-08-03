<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function view(Request $request)
    {
        return inertia('auth/Dashboard');
    }
}
