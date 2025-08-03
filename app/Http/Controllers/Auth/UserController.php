<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;

class UserController extends Controller
{
    // Display the list of users
    public function view()
    {
        return Inertia::render('auth/User');
    }

    // Retrieve all users
    public function all(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get('start') ?? 0;
        $length = $request->get('length') ?? 10;
        $search = $request->get('search') ?? '';
        $orderColumn = $request->get('order_column') ?? 'id';
        $orderDir = $request->get('order_dir') ?? 'asc';

        $query = User::query();

        // Search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        // With relationship
        $query->with('role');
        // Total records before filtering
        $totalRecords = User::count();

        // Total records after filtering
        $filteredRecords = $query->count();

        // Get data with pagination and ordering
        $data = $query->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    // Display the create user view
    public function create_view()
    {
        return Inertia::render('auth/User/CreateUser');
    }
    // Create a new user
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ];

            if ($request->role_id) {
                $userData['role_id'] = $request->role_id;
            }

            $user = User::create($userData);
            return back()
                ->withCookie(Cookie::make('notyf_flash_success', 'User created successfully.', 1));
        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('name', 'email', 'role_id'))
                ->withCookie(Cookie::make('notyf_flash_error', 'Failed to create user.', 1));
        }
    }
    // Display the detail view
    public function detail_view(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'User ID is required.', 1));
        }
        $user = User::find($id);
        if (!$user) {
            return back()
                ->withCookie(Cookie::make(
                    'notyf_flash_error',
                    'User not found.',
                    1
                ));
        }

        return Inertia::render('auth/User/DetailUser', [
            'user' => $user->load('role')
        ]);
    }
    // Delete users
    public function delete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:users,id'
        ]);

        $userIds = $request->input('ids', []);

        if (empty($userIds)) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'No users selected for deletion.', 1));
        }

        try {
            $deletedCount = User::whereIn('id', $userIds)->delete();

            if ($deletedCount > 0) {
                return back()
                    ->withCookie(Cookie::make('notyf_flash_success', "Successfully deleted {$deletedCount} user(s).", 1));
            } else {
                return back()
                    ->withCookie(Cookie::make('notyf_flash_error', 'No users were deleted.', 1));
            }
        } catch (\Exception $e) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'Failed to delete users.', 1));
        }
    }

    // Get specific user
    public function get(Request $request)
    {
        $id = $request->get('id');

        if (!$id) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'User ID is required.', 1));
        }

        $user = User::find($id);

        if (!$user) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'User not found.', 1));
        }

        return Inertia::render('auth/User', [
            'user' => $user
        ]);
    }

    // update user view
    public function update_view(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'User ID is required.', 1));
        }
        $user = User::find($id);
        if (!$user) {
            return back()
                ->withCookie(Cookie::make(
                    'notyf_flash_error',
                    'User not found.',
                    1
                ));
        }

        return Inertia::render('auth/User/UpdateUser', [
            'user' => $user->load('role')
        ]);
    }
    // Update user
    public function update(Request $request)
    {
        $id = $request->input('id') ?? $request->get('id');

        $request->validate([
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        try {
            $user = User::findOrFail($id);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->password) {
                $userData['password'] = bcrypt($request->password);
            }

            if ($request->has('role_id')) {
                $userData['role_id'] = $request->role_id;
            }

            $user->update($userData);

            return back()
                ->withCookie(Cookie::make('notyf_flash_success', 'User updated successfully.', 1));
        } catch (\Exception $e) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'Failed to update user.', 1));
        }
    }
    // Export users
    public function export(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'fields' => 'nullable|array',
            'format' => 'required|in:csv,excel,pdf'
        ]);

        $userIds = $request->input('ids', []);
        $format = $request->input('format', 'csv');
        $fields = $request->input('fields', []);
        if ($request->input('all')) {
            $userIds = User::pluck('id')->toArray();
        }
        if (empty($userIds)) {
            return response()->json(['message' => 'No users selected for export.'], 422);
        }
        if (empty($fields)) {
            return response()->json(['message' => 'No fields selected for export.'], 422);
        }
        try {
            // Logic to export users based on the format
            // This is a placeholder, implement actual export logic here
            return response()->json(['message' => 'Export received.. processing now']);
        } catch (\Exception $e) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'Failed to export users.', 1));
        }
    }
}
