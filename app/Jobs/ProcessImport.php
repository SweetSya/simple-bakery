<?php

namespace App\Jobs;

use App\Imports\UsersImport;
use App\Models\JobWatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProcessImport implements ShouldQueue
{
    use Queueable;

    protected $fieldMapping;
    protected $uniqueId;
    protected $file;
    protected $entityName;
    /**
     * Create a new job instance.
     */
    public function __construct($entityName, string $uniqueId, array $fieldMapping, $file)
    {
        $this->uniqueId = $uniqueId;
        $this->fieldMapping = $fieldMapping;
        $this->file = $file;
        $this->entityName = $entityName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            JobWatcher::where('job_id', $this->uniqueId)->update([
                'status' => 'processing',
            ]);
            Log::info($this->fieldMapping);
            Excel::import(new UsersImport($this->fieldMapping['mappings']), $this->file);

            JobWatcher::where('job_id', $this->uniqueId)->update([
                'status' => 'completed',
                'job_data' => [
                    'title' => "Import completed ({$this->entityName})",
                    'message' => 'Import completed successfully',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('import job failed: ' . $e->getMessage(), [
                'job_id' => $this->uniqueId,
                'trace' => $e->getTraceAsString()
            ]);
            JobWatcher::where('job_id', $this->uniqueId)->update([
                'status' => 'failed',
                'job_data' => [
                    'title' => "Import failed ({$this->entityName})",
                    'message' => 'Import failed: ' . $e->getMessage(),
                    'title' => 'Import failed for table (' . $this->entityName . ')',
                ],
            ]);
        }
    }
}
