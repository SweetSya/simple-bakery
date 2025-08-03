<?php

namespace App\Http\Controllers\Auth;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessExport;
use App\Jobs\ProcessImport;
use App\Models\JobWatcher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
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
            'fields' => 'nullable|array',
            'format' => 'required|in:csv,excel,pdf'
        ]);

        $userIds = $request->input('ids', []);
        $format = $request->input('format', 'csv');
        $fields = $request->input('fields', []);
        if ($request->input('export_all')) {
            $userIds = User::pluck('id')->toArray();
        }
        if (empty($userIds)) {
            return response()->json(['message' => 'No users selected for export.'], 422);
        }
        if (empty($fields)) {
            return response()->json(['message' => 'No fields selected for export.'], 422);
        }
        try {
            //Logic to export users based on the format
            $uniqueId = time() . '-' . uniqid();
            ProcessExport::dispatch('Users', $userIds, $fields, $format, $uniqueId, UsersExport::class)->onQueue('default');
            // This is a placeholder, implement actual exportLogic here
            JobWatcher::create([
                'job_id' => $uniqueId,
                'user_id' => Auth::user()->id,
                'job_type' => 'export',
                'job_data' => [],
            ]);
            return response()->json(['message' => 'Export received.. processing now', 'total' => count($userIds)], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to process export.', 'error' => $e->getMessage()], 500);
        }
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240' // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            if ($extension === 'csv') {
                $data = $this->processCsvFile($file);
            } else {
                $data = $this->processExcelFile($file);
            }

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found in the file'
                ], 422);
            }

            if (count($data) > 1000) {
                return response()->json([
                    'success' => false,
                    'message' => 'File contains too many rows. Maximum 1000 rows allowed.'
                ], 422);
            }

            $headers = array_keys($data[0]);
            $preview = array_slice($data, 0, 5); // Show first 5 rows for preview

            return response()->json([
                'success' => true,
                'headers' => $headers,
                'preview' => $preview,
                'total_rows' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Import preview failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'field_mapping' => 'required|json'
        ]);

        try {
            $file = $request->file('file');
            $fieldMapping = json_decode($request->input('field_mapping'), true);

            if (empty($fieldMapping)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field mapping is required'
                ], 422);
            }

            // Validate required fields are mapped
            $requiredFields = ['name', 'email'];
            $mappedDbFields = array_values($fieldMapping);

            foreach ($requiredFields as $required) {
                if (!in_array($required, $mappedDbFields)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Required field '{$required}' must be mapped"
                    ], 422);
                }
            }

            $finalMapping = json_decode($request->column_mapping, true);
            // Store the file temporarily and pass the path instead of the file object
            $uniqueId = time() . '-' . uniqid();
            $fileName = "import_{$uniqueId}.xlsx";
            $filePath = "imports/{$fileName}";

            // Store file in local storage
            Storage::disk('local')->put($filePath, file_get_contents($file->getRealPath()));
            ProcessImport::dispatch('Users', $uniqueId, $finalMapping, $filePath)
                ->onQueue('default');

            JobWatcher::create([
                'job_id' => $uniqueId,
                'user_id' => Auth::user()->id,
                'job_type' => 'import',
                'job_data' => [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Import received.. processing now',

            ]);
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processCsvFile($file): array
    {
        $path = $file->getRealPath();
        $data = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle);

            if (!$headers) {
                throw new \Exception('Invalid CSV file format');
            }

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, $row);
                }
            }

            fclose($handle);
        }

        return $data;
    }

    private function processExcelFile($file): array
    {
        // You'll need to install PhpSpreadsheet: composer require phpoffice/phpspreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];

        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        // Get headers from first row
        $headers = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $headers[] = $worksheet->getCell($col . '1')->getValue();
        }

        // Get data rows
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $rowData[] = $worksheet->getCell($col . $row)->getValue();
            }

            if (count($rowData) === count($headers)) {
                $data[] = array_combine($headers, $rowData);
            }
        }

        return $data;
    }

    private function processImportData(array $data, array $fieldMapping): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                $userData = [];

                // Map fields according to field mapping
                foreach ($fieldMapping as $csvField => $dbField) {
                    if (isset($row[$csvField])) {
                        $value = trim($row[$csvField]);

                        // Handle special fields
                        switch ($dbField) {
                            case 'email':
                                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                    throw new \Exception("Invalid email format: {$value}");
                                }
                                $userData[$dbField] = $value;
                                break;

                            case 'password':
                                if (!empty($value)) {
                                    $userData[$dbField] = bcrypt($value);
                                }
                                break;

                            case 'role_id':
                                if (!empty($value)) {
                                    // Validate role exists
                                    $role = \App\Models\Role::find($value);
                                    if (!$role) {
                                        throw new \Exception("Role with ID {$value} not found");
                                    }
                                    $userData[$dbField] = $value;
                                }
                                break;

                            case 'email_verified_at':
                                if (!empty($value) && in_array(strtolower($value), ['yes', 'true', '1', 'verified'])) {
                                    $userData[$dbField] = now();
                                }
                                break;

                            default:
                                if (!empty($value)) {
                                    $userData[$dbField] = $value;
                                }
                        }
                    }
                }

                // Validate required fields
                if (empty($userData['name']) || empty($userData['email'])) {
                    throw new \Exception('Name and email are required');
                }

                // Check if user already exists
                $existingUser = User::where('email', $userData['email'])->first();
                if ($existingUser) {
                    $skipped++;
                    continue;
                }

                // Set default password if not provided
                if (!isset($userData['password'])) {
                    $userData['password'] = bcrypt('password123');
                }

                // Create user
                User::create($userData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                $skipped++;
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }
}
