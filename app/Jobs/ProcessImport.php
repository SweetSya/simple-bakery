<?php

namespace App\Jobs;

use App\Imports\UsersImport;
use App\Models\JobWatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;

class ProcessImport implements ShouldQueue
{
    use Queueable;

    protected $fieldMapping;
    protected $uniqueId;
    protected $file;
    protected $table;
    /**
     * Create a new job instance.
     */
    public function __construct($table, string $uniqueId, array $fieldMapping, $file)
    {
        $this->uniqueId = $uniqueId;
        $this->fieldMapping = $fieldMapping;
        $this->file = $file;
        $this->table = $table;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        JobWatcher::where('job_id', $this->uniqueId)->update([
            'status' => 'processing',
        ]);

        Excel::import(new UsersImport($this->fieldMapping['mappings']), $this->file);

        JobWatcher::where('job_id', $this->uniqueId)->update([
            'status' => 'completed',
            'job_data' => [
                'message' => 'Import completed successfully',
                'title' => 'Import completed (' . $this->table . ')',
            ],
        ]);
    }
}
