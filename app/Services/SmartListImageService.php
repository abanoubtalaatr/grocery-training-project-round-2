<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class SmartListImageService
{
    /**
     * Upload the given image to the smart-lists images directory
     * and return the generated filename.
     */
    public function upload(UploadedFile $image): string
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/smart-lists'), $imageName);

        return $imageName;
    }
}