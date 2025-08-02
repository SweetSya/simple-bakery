<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegisterController extends Controller
{
    // display register view
    public function view()
    {
        return Inertia::render('Register');
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Create the user
        $user = User::create([
            'name'  => $request->name ?? 'Guest',
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => Role::where('name', 'guest')->first()->id,
        ]);

        return redirect()->route('login')->with('success', 'Registration successful. Please log in.');
    }
}
