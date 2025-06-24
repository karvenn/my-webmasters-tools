<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CloudflareR2Service
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('r2');
    }

    public function uploadScreenshot(UploadedFile $file, int $formId, int $submissionId): ?string
    {
        try {
            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(40) . '.' . $extension;
            $path = "uat-forms/{$formId}/{$submissionId}/{$filename}";

            $uploaded = $this->disk->put($path, $file->getContent(), 'public');

            if ($uploaded) {
                return $this->disk->url($path);
            }

            return null;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function deleteScreenshot(string $url): bool
    {
        try {
            $path = $this->getPathFromUrl($url);
            if ($path) {
                return $this->disk->delete($path);
            }
            return false;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
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
            
            $uploaded = $this->disk->put($path, $fileData, 'public');
            
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

    protected function getPathFromUrl(string $url): ?string
    {
        $baseUrl = config('filesystems.disks.r2.url');
        if (str_starts_with($url, $baseUrl)) {
            return substr($url, strlen($baseUrl) + 1);
        }
        return null;
    }
}