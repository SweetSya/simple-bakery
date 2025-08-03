<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    /**
     * Download file from storage
     */
    public function export(Request $request, $filename)
    {
        try {
            // Validate filename to prevent directory traversal
            $filename = basename($filename);

            // Define the storage path (adjust based on your storage structure)
            $filePath = 'exports/' . $filename;

            // Check if file exists
            if (!Storage::disk('local')->exists($filePath)) {
                dd('File not found: ' . $filePath);
                abort(404, 'File not found');
            }

            // Get the full path
            $fullPath = Storage::disk('local')->path($filePath);

            // Determine the file's MIME type
            $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

            // Create response with proper headers
            return Response::download($fullPath, $filename, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Download failed: ' . $e->getMessage());
            abort(500, 'Download failed');
        }
    }
}
