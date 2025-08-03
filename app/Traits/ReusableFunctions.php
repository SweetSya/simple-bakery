<?php

namespace App\Traits;

use App\Jobs\ProcessExport;
use App\Jobs\ProcessImport;
use App\Models\JobWatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

trait ReusableFunctions
{
    /**
     * Get export class for the entity
     */
    abstract protected function getExportClass(): string;

    /**
     * Get import fields mapping
     */
    abstract protected function getImportFieldMapping(): array;

    /**
     * Each controller should implement this method
     */
    abstract protected function getRequiredImportFields(): array;

    /**
     * Export records
     */
    public function export(Request $request)
    {
        $request->validate([
            'fields' => 'nullable|array',
            'format' => 'required|in:csv,excel,pdf'
        ]);

        $recordIds = $request->input('ids', []);
        $format = $request->input('format', 'csv');
        $fields = $request->input('fields', []);

        if ($request->input('export_all')) {
            $modelClass = $this->getModelClass();
            $recordIds = $modelClass::pluck('id')->toArray();
        }

        if (empty($recordIds)) {
            return response()->json(['message' => 'No records selected for export.'], 422);
        }

        if (empty($fields)) {
            return response()->json(['message' => 'No fields selected for export.'], 422);
        }

        try {
            $uniqueId = time() . '-' . uniqid();
            $entityName = $this->getEntityName();

            // Create job watcher first
            JobWatcher::create([
                'job_id' => $uniqueId,
                'user_id' => Auth::user()->id,
                'job_type' => 'export',
                'status' => 'pending',
                'job_data' => [
                    'entity' => $entityName,
                    'total_records' => count($recordIds),
                    'format' => $format,
                    'message' => 'Export job queued...'
                ],
            ]);

            // Dispatch the export job
            ProcessExport::dispatch(
                $entityName,
                $recordIds,
                $fields,
                $format,
                $uniqueId,
                $this->getExportClass()
            )->onQueue('default');

            return response()->json([
                'message' => 'Export job started successfully! ',
                'job_id' => $uniqueId,
                'total' => count($recordIds)
            ], 200);
        } catch (\Exception $e) {
            Log::error("Export failed for {$this->getEntityName()}: " . $e->getMessage());

            return response()->json([
                'message' => 'Failed to process export.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import preview
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $data = $this->processExcelFile($file);

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
            $preview = array_slice($data, 0, 5);

            return response()->json([
                'success' => true,
                'headers' => $headers,
                'preview' => $preview,
                'total_rows' => count($data),
                'available_fields' => $this->getImportFieldMapping()
            ]);
        } catch (\Exception $e) {
            Log::error("Import preview failed for {$this->getEntityName()}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import records
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'field_mapping' => 'required|json',
            'column_mapping' => 'required|json'
        ]);

        try {
            $file = $request->file('file');
            $fieldMapping = json_decode($request->input('field_mapping'), true);
            $columnMapping = json_decode($request->input('column_mapping'), true);

            if (empty($fieldMapping) || empty($columnMapping)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field mapping and column mapping are required'
                ], 422);
            }

            // Validate required fields - now uses the controller's implementation
            $requiredFields = $this->getRequiredImportFields();
            $mappedDbFields = array_keys($columnMapping['mappings']);

            foreach ($requiredFields as $required) {
                if (!in_array($required, $mappedDbFields)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Required field '{$required}' must be mapped"
                    ], 422);
                }
            }

            // Store file temporarily
            $uniqueId = time() . '-' . uniqid();
            $fileName = "import_{$uniqueId}.xlsx";
            $filePath = "imports/{$fileName}";

            Storage::disk('local')->put($filePath, file_get_contents($file->getRealPath()));

            // Get total rows for progress tracking
            $data = $this->processExcelFile($file);
            $totalRows = count($data);

            // Create job watcher first
            JobWatcher::create([
                'job_id' => $uniqueId,
                'user_id' => Auth::user()->id,
                'job_type' => 'import',
                'status' => 'pending',
                'job_data' => [
                    'entity' => $this->getEntityName(),
                    'total_rows' => $totalRows,
                    'file_name' => $fileName,
                    'message' => 'Import job queued...'
                ],
            ]);

            // Dispatch the import job
            ProcessImport::dispatch(
                $this->getEntityName(),
                $uniqueId,
                $mappedDbFields,
                $filePath,
                $totalRows
            )->onQueue('default');

            return response()->json([
                'success' => true,
                'message' => 'Import job started successfully! ',
                'job_id' => $uniqueId,
                'total_rows' => $totalRows
            ]);
        } catch (\Exception $e) {
            Log::error("Import failed for {$this->getEntityName()}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Excel file
     */
    private function processExcelFile($file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];

        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        // Get headers from first row
        $headers = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $cellValue = $worksheet->getCell($col . '1')->getValue();
            $headers[] = $cellValue ? trim(strval($cellValue)) : '';
        }

        // Filter out empty headers
        $headers = array_filter($headers);

        // Get data rows
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = [];
            $colIndex = 0;

            for ($col = 'A'; $col <= $highestColumn; $col++) {
                if ($colIndex < count($headers)) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    $rowData[$headers[$colIndex]] = $cellValue ? trim(strval($cellValue)) : '';
                }
                $colIndex++;
            }

            // Only add row if it has at least one non-empty value
            if (array_filter($rowData)) {
                $data[] = $rowData;
            }
        }

        return $data;
    }
}
