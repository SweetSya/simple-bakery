<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RoleController extends Controller
{
    // Display the list of roles
    public function view()
    {
        return Inertia::render('auth/Role');
    }

    // Retrieve all roles
    public function all(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get('start') ?? 0;
        $length = $request->get('length') ?? 10;
        $search = $request->get('search') ?? '';
        $orderColumn = $request->get('order_column') ?? 'id';
        $orderDir = $request->get('order_dir') ?? 'asc';

        $query = Role::query();

        // Search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Total records before filtering
        $totalRecords = Role::count();

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

    // Display the create role view
    public function create_view()
    {
        return Inertia::render('auth/Role/CreateRole');
    }
    // Create a new role
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $role = Role::create($request->only('name', 'description'));

            return back()
                ->withCookie(Cookie::make('notyf_flash_success', 'Role created successfully.', 1));
        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('name', 'description'))
                ->withCookie(Cookie::make('notyf_flash_error', 'Failed to create role.', 1));
        }
    }

    // Delete roles
    public function delete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:roles,id'
        ]);

        $roleIds = $request->input('ids', []);

        if (empty($roleIds)) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'No roles selected for deletion.', 1));
        }

        try {
            $deletedCount = Role::whereIn('id', $roleIds)->delete();

            if ($deletedCount > 0) {
                return back()
                    ->withCookie(Cookie::make('notyf_flash_success', "Successfully deleted {$deletedCount} role(s).", 1));
            } else {
                return back()
                    ->withCookie(Cookie::make('notyf_flash_error', 'No roles were deleted.', 1));
            }
        } catch (\Exception $e) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'Failed to delete roles.', 1));
        }
    }

    // Get specific role
    public function get(Request $request)
    {
        $id = $request->get('id');

        if (!$id) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'Role ID is required.', 1));
        }

        $role = Role::find($id);

        if (!$role) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'Role not found.', 1));
        }

        return Inertia::render('auth/Role', [
            'role' => $role
        ]);
    }

    // update role view
    public function update_view(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'Role ID is required.', 1));
        }
        $role = Role::find($id);
        if (!$role) {
            return back()
                ->withCookie(Cookie::make(
                    'notyf_flash_error',
                    'Role not found.',
                    1
                ));
        }

        return Inertia::render('auth/Role/UpdateRole', [
            'role' => $role
        ]);
    }
    // Update role
    public function update(Request $request)
    {
        $id = $request->input('id');

        $request->validate([
            'id' => 'required|exists:roles,id',
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $role = Role::findOrFail($id);
            $role->update($request->only('name', 'description'));

            return back()
                ->withCookie(Cookie::make('notyf_flash_success', 'Role updated successfully.', 1));
        } catch (\Exception $e) {
            return back()
                ->withCookie(Cookie::make('notyf_flash_error', 'Failed to update role.', 1));
        }
    }
}
