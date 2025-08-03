<?php

namespace App\Jobs;

use App\Models\JobWatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessExport implements ShouldQueue
{
    use Queueable;

    protected $entityName;
    protected $recordIds;
    protected $fields;
    protected $format;
    protected $uniqueId;
    protected $exportClass;

    /**
     * Create a new job instance.
     */
    public function __construct(string $entityName, array $recordIds, array $fields, string $format, string $uniqueId, string $exportClass)
    {
        $this->entityName = $entityName;
        $this->recordIds = $recordIds;
        $this->fields = $fields;
        $this->format = $format;
        $this->uniqueId = $uniqueId;
        $this->exportClass = $exportClass;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update job status
            $jobWatcher = JobWatcher::where('job_id', $this->uniqueId)->first();

            if (!$jobWatcher) {
                Log::error("Job watcher not found for job ID: {$this->uniqueId}");
                return;
            }

            $jobWatcher->update([
                'status' => 'processing',
                'job_data' => array_merge($jobWatcher->job_data, [
                    'message' => "Processing export of {$this->entityName}...",
                ])
            ]);

            // Create export instance
            $exportInstance = new $this->exportClass($this->recordIds, $this->fields);

            // Generate filename
            $filename = $this->generateFilename();
            $filePath = "exports/{$filename}";

            // Export based on format
            switch ($this->format) {
                case 'excel':
                    Excel::store($exportInstance, $filePath, 'local', \Maatwebsite\Excel\Excel::XLSX);
                    break;
                case 'csv':
                    Excel::store($exportInstance, $filePath, 'local', \Maatwebsite\Excel\Excel::CSV);
                    break;
                case 'pdf':
                    Excel::store($exportInstance, $filePath, 'local', \Maatwebsite\Excel\Excel::DOMPDF);
                    break;
                default:
                    throw new \Exception("Unsupported export format: {$this->format}");
            }

            // Update job with success
            $jobWatcher->update([
                'status' => 'completed',
                'job_data' => array_merge($jobWatcher->job_data, [
                    'title' => "Export completed ({$this->entityName})",
                    'message' => "Export completed successfully!",
                    'file_path' => $filePath,
                    'file_name' => $filename,
                    'download_url' => route('download.export', ['file' => $filename]),
                    'completed_at' => now()->toISOString()
                ])
            ]);
        } catch (\Exception $e) {
            Log::error('Export job failed: ' . $e->getMessage(), [
                'job_id' => $this->uniqueId,
                'entity' => $this->entityName,
                'trace' => $e->getTraceAsString()
            ]);
            $jobWatcher->update([
                'status' => 'failed',
                'job_data' => array_merge($jobWatcher->job_data ?? [], [
                    'title' => "Export failed ({$this->entityName})",
                    'message' => 'Export failed: ' . $e->getMessage(),
                    'failed_at' => now()->toISOString()
                ])
            ]);

            throw $e;
        }
    }

    /**
     * Generate filename for export
     */
    private function generateFilename(): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $extension = $this->getFileExtension();

        return "{$this->entityName}_export_{$timestamp}.{$extension}";
    }

    /**
     * Get file extension based on format
     */
    private function getFileExtension(): string
    {
        switch ($this->format) {
            case 'excel':
                return 'xlsx';
            case 'csv':
                return 'csv';
            case 'pdf':
                return 'pdf';
            default:
                return 'xlsx';
        }
    }
}
