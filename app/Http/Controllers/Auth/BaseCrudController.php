<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

abstract class BaseCrudController extends Controller
{
    /**
     * The model class name
     */
    abstract protected function getModelClass(): string;

    /**
     * The base view path (e.g., 'auth/User')
     */
    abstract protected function getViewPath(): string;

    /**
     * Get validation rules for create
     */
    abstract protected function getCreateValidationRules(): array;

    /**
     * Get validation rules for update
     */
    abstract protected function getUpdateValidationRules(int $id): array;

    /**
     * Get search fields for filtering
     */
    protected function getSearchFields(): array
    {
        return ['name'];
    }

    /**
     * Get relationships to load
     */
    protected function getRelationships(): array
    {
        return [];
    }

    /**
     * Transform data before create
     */
    protected function transformCreateData(array $data): array
    {
        return $data;
    }

    /**
     * Transform data before update
     */
    protected function transformUpdateData(array $data): array
    {
        return $data;
    }

    /**
     * Get additional data for views
     */
    protected function getAdditionalViewData(): array
    {
        return [];
    }

    /**
     * Display the list view
     */
    public function view()
    {
        return Inertia::render($this->getViewPath(), $this->getAdditionalViewData());
    }

    /**
     * Retrieve all records with DataTables support
     */
    public function all(Request $request)
    {
        $modelClass = $this->getModelClass();
        $draw = $request->get('draw');
        $start = $request->get('start') ?? 0;
        $length = $request->get('length') ?? 10;
        $search = $request->get('search') ?? '';
        $orderColumn = $request->get('order_column') ?? 'id';
        $orderDir = $request->get('order_dir') ?? 'asc';

        $query = $modelClass::query();

        // Apply relationships
        if (!empty($this->getRelationships())) {
            $query->with($this->getRelationships());
        }

        // Search
        if (!empty($search)) {
            $searchFields = $this->getSearchFields();
            $query->where(function ($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        // Total records before filtering
        $totalRecords = $modelClass::count();

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

    /**
     * Display the create view
     */
    public function create_view()
    {
        return Inertia::render($this->getViewPath() . '/Create', $this->getAdditionalViewData());
    }

    /**
     * Create a new record
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getCreateValidationRules());

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->withCookie(
                    Cookie::make('notyf_flash_error', 'Validation failed. Please check your input.', 1)
                );
        }

        try {
            $modelClass = $this->getModelClass();
            $data = $this->transformCreateData($validator->validated());

            $record = $modelClass::create($data);

            return back()->withCookie(
                Cookie::make('notyf_flash_success', $this->getEntityName() . ' created successfully.', 1)
            );
        } catch (\Exception $e) {
            Log::error("Failed to create {$this->getEntityName()}: " . $e->getMessage());

            return back()
                ->withInput()
                ->withCookie(
                    Cookie::make('notyf_flash_error', 'Failed to create ' . strtolower($this->getEntityName()) . '.', 1)
                );
        }
    }

    /**
     * Display the detail view
     */
    public function detail_view(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return back()->withCookie(
                Cookie::make('notyf_flash_error', $this->getEntityName() . ' ID is required.', 1)
            );
        }

        $modelClass = $this->getModelClass();
        $record = $modelClass::with($this->getRelationships())->find($id);

        if (!$record) {
            return back()->withCookie(
                Cookie::make('notyf_flash_error', $this->getEntityName() . ' not found.', 1)
            );
        }

        $viewData = array_merge([
            strtolower($this->getEntityName()) => $record,
        ], $this->getAdditionalViewData());

        // Add audit logs if model supports auditing
        if (method_exists($record, 'audits')) {
            $viewData['auditLogs'] = $this->getFormattedAuditLogs($record);
        }

        return Inertia::render($this->getViewPath() . '/Detail', $viewData);
    }

    /**
     * Display the update view
     */
    public function update_view(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return back()->withCookie(
                Cookie::make('notyf_flash_error', $this->getEntityName() . ' ID is required.', 1)
            );
        }

        $modelClass = $this->getModelClass();
        $record = $modelClass::with($this->getRelationships())->find($id);

        if (!$record) {
            return back()->withCookie(
                Cookie::make('notyf_flash_error', $this->getEntityName() . ' not found.', 1)
            );
        }

        $viewData = array_merge([
            strtolower($this->getEntityName()) => $record,
        ], $this->getAdditionalViewData());

        // Add audit logs if model supports auditing
        if (method_exists($record, 'audits')) {
            $viewData['auditLogs'] = $this->getFormattedAuditLogs($record);
        }

        return Inertia::render($this->getViewPath() . '/Update', $viewData);
    }

    /**
     * Update a record
     */
    public function update(Request $request)
    {
        $id = $request->input('id') ?? $request->get('id');

        if (!$id) {
            return back()->withCookie(
                Cookie::make('notyf_flash_error', $this->getEntityName() . ' ID is required.', 1)
            );
        }

        $validator = Validator::make($request->all(), $this->getUpdateValidationRules($id));

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->withCookie(
                    Cookie::make('notyf_flash_error', 'Validation failed. Please check your input.', 1)
                );
        }

        try {
            $modelClass = $this->getModelClass();
            $record = $modelClass::findOrFail($id);

            $data = $this->transformUpdateData($validator->validated());
            $record->update($data);

            return back()->withCookie(
                Cookie::make('notyf_flash_success', $this->getEntityName() . ' updated successfully.', 1)
            );
        } catch (\Exception $e) {
            Log::error("Failed to update {$this->getEntityName()}: " . $e->getMessage());

            return back()
                ->withInput()
                ->withCookie(
                    Cookie::make('notyf_flash_error', 'Failed to update ' . strtolower($this->getEntityName()) . '.', 1)
                );
        }
    }

    /**
     * Delete records
     */
    public function delete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:' . $this->getTableName() . ',id'
        ]);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->withCookie(
                Cookie::make('notyf_flash_error', 'No ' . strtolower($this->getEntityName()) . 's selected for deletion.', 1)
            );
        }

        try {
            $modelClass = $this->getModelClass();
            $deletedCount = $modelClass::whereIn('id', $ids)->delete();

            if ($deletedCount > 0) {
                return back()->withCookie(
                    Cookie::make('notyf_flash_success', "Successfully deleted {$deletedCount} " . strtolower($this->getEntityName()) . "(s).", 1)
                );
            } else {
                return back()->withCookie(
                    Cookie::make('notyf_flash_error', 'No ' . strtolower($this->getEntityName()) . 's were deleted.', 1)
                );
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete {$this->getEntityName()}s: " . $e->getMessage());

            return back()->withCookie(
                Cookie::make('notyf_flash_error', 'Failed to delete ' . strtolower($this->getEntityName()) . 's.', 1)
            );
        }
    }

    /**
     * Get entity name for messages
     */
    protected function getEntityName(): string
    {
        return class_basename($this->getModelClass());
    }

    /**
     * Get table name for validation
     */
    protected function getTableName(): string
    {
        $modelClass = $this->getModelClass();
        return (new $modelClass)->getTable();
    }

    /**
     * Get formatted audit logs
     */
    protected function getFormattedAuditLogs($record, int $limit = 20): array
    {
        if (!method_exists($record, 'audits')) {
            return [];
        }

        return $record->audits()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($audit) {
                return [
                    'id' => $audit->id,
                    'event' => $audit->event,
                    'action' => $this->formatAuditAction($audit),
                    'old_values' => $audit->old_values,
                    'new_values' => $audit->new_values,
                    'user_name' => $audit->user ? $audit->user->name : 'System',
                    'ip_address' => $audit->ip_address,
                    'user_agent' => $audit->user_agent,
                    'url' => $audit->url,
                    'created_at' => $audit->created_at->toISOString(),
                    'created_at_human' => $audit->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }

    /**
     * Format audit action for display
     */
    protected function formatAuditAction($audit): string
    {
        $action = ucfirst($audit->event);
        $changes = [];

        if ($audit->event === 'updated' && $audit->old_values && $audit->new_values) {
            foreach ($audit->new_values as $field => $newValue) {
                $oldValue = $audit->old_values[$field] ?? 'N/A';
                $fieldName = ucwords(str_replace('_', ' ', $field));
                $changes[] = "({$fieldName}) {$oldValue} → {$newValue}";
            }
        }

        if (!empty($changes)) {
            return $action . ': ' . implode(', ', $changes);
        }

        return $action;
    }
}
