<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesImageUploads
{
    protected function storeUploadedImage(UploadedFile $image, string $folder, ?string $oldPath = null, string $disk = 'public'): string
    {
        if ($oldPath) {
            Storage::disk($disk)->delete($oldPath);
        }

        return $image->store($folder, $disk);
    }
}