<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    protected $model = Role::class;

    // Display the list of roles
    public function view()
    {
        return Inertia::render('auth/Role');
    }
    // Retrive all roles
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

    public function delete(Request $request)
    {
        $roleIds = $request->input('ids', []);
        if (empty($roleIds)) {
            return response()->json(['message' => 'No roles selected for deletion'], 400);
        }
        Role::whereIn('id', $roleIds)->delete();
        return response()->json(['message' => 'Roles deleted successfully']);
    }
}
