<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalStorageService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('public');
    }

    public function uploadBase64File(string $base64Data, int $formId, int $submissionId, string $originalFilename): ?array
    {
        try {
            $mimeType = 'application/octet-stream';
            $data = $base64Data;
            
            // Check if it's a data URI
            if (preg_match('/^data:([^;]+);base64,(.+)$/', $base64Data, $matches)) {
                $mimeType = $matches[1];
                $data = $matches[2];
            }
            
            $fileData = base64_decode($data);
            
            if ($fileData === false) {
                return null;
            }
            
            // Get extension from original filename
            $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            $filename = Str::random(40) . '.' . $extension;
            $path = "uat-forms/{$formId}/{$submissionId}/{$filename}";
            
            $uploaded = $this->disk->put($path, $fileData);
            
            if ($uploaded) {
                return [
                    'filename' => $path,
                    'original_filename' => $originalFilename,
                    'mime_type' => $mimeType,
                    'size' => strlen($fileData),
                    'url' => $this->disk->url($path),
                ];
            }
            
            return null;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }
}