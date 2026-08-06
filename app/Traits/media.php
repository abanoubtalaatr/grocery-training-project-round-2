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
        if (Storage::disk('public')->exists($imgPath)) {
            return Storage::disk('public')->delete($imgPath);
        }
        return false;
    }
}
