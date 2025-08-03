<?php

namespace App\Jobs;

use App\Models\JobWatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat; // alias for constants

class ProcessExport implements ShouldQueue
{
    use Queueable;

    protected $ids = [];
    protected $fields = [];
    protected $exports;
    protected $type;
    protected $uniqueId;
    protected $table;

    public function __construct(string $table, array $ids = [], array $fields = [], string $type = 'excel', string $uniqueId = '', $exports)
    {
        $this->table = $table;
        $this->ids = $ids;
        $this->fields = $fields;
        $this->exports = $exports; // class name like UsersExport::class
        $this->uniqueId = $uniqueId;
        $this->type = $type === 'csv' ? 'csv' : 'xlsx'; // string for file extension
    }

    public function handle(): void
    {
        JobWatcher::where('job_id', $this->uniqueId)->update([
            'status' => 'processing',
        ]);

        // Determine the correct writer type
        $writerType = $this->type === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;

        // Store the export file
        Excel::store(
            new $this->exports($this->ids, $this->fields),
            'exports/' . $this->uniqueId . '.' . $this->type,
            null,
            $writerType
        );

        JobWatcher::where('job_id', $this->uniqueId)->update([
            'status' => 'completed',
            'job_data' => [
                'download' => route('download.file', [
                    'filename' => $this->uniqueId . '.' . $this->type,
                ]),
                'file_path' => 'exports/' . $this->uniqueId . '.' . $this->type,
                'file_name' => $this->uniqueId . '.' . $this->type,
                'message' => 'Your export has been completed successfully. Total there is ' . count($this->ids) . ' records exported.',
                'title' => 'Export completed (' . $this->table . ')',
            ],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        JobWatcher::where('job_id', $this->uniqueId)->update([
            'status' => 'failed',
            'job_data' => [
                'message' => $exception->getMessage(),
                'title' => 'Export failed.',
            ],
        ]);

        Log::error("Export job failed [{$this->uniqueId}]: " . $exception->getMessage());
    }
}
