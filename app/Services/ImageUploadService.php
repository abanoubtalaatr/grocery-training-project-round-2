<?php

namespace App\Services;
use Illuminate\Http\UploadedFile;
class ImageUploadService
{
    public function upload(UploadedFile $file, string $folder): string
    {
        $imageName = $this->generateFileName($file);

        $file->move(public_path("images/{$folder}"), $imageName);

        return $imageName;
    }
        public function delete(?string $fileName, string $folder): void
    {
        if (!$fileName) {
            return;
        }

        $path = public_path("images/{$folder}/{$fileName}");

        if (file_exists($path)) {
            unlink($path);
        }
    }
        private function generateFileName(UploadedFile $file): string
    {
        return time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
    }

}
