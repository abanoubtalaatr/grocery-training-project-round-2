<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait Media
{
    public function uploadPhoto(UploadedFile $img, string $dir): string
    {
        return $img->store($dir, 'public');
    }

    public function deletePhoto(string $imgPath): bool
    {
        // Avoid deleting external URLs
        if (filter_var($imgPath, FILTER_VALIDATE_URL)) {
            return false;
        }

        if (Storage::disk('public')->exists($imgPath)) {
            return Storage::disk('public')->delete($imgPath);
        }
        return false;
    }

    /**
     * Handle image upload (supports file upload or external URL).
     * If an uploaded file is provided, stores it on the public disk.
     * If an external URL is provided, returns it as-is.
     * Deletes the old image if a new one is successfully uploaded.
     */
    public function handleImageUpload($fileOrUrl, string $dir, ?string $oldPath = null): ?string
    {
        if ($fileOrUrl instanceof UploadedFile) {
            if ($oldPath) {
                $this->deletePhoto($oldPath);
            }
            return $this->uploadPhoto($fileOrUrl, $dir);
        }

        if (is_string($fileOrUrl) && filter_var($fileOrUrl, FILTER_VALIDATE_URL)) {
            if ($oldPath) {
                $this->deletePhoto($oldPath);
            }
            return $fileOrUrl;
        }

        return $oldPath;
    }
}

