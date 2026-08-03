<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaUploadService{

public function updload_file(UploadedFile $file ,string $directory='uplods_imges',string $disk='public'){
    return $file->store($directory,$disk);
}

public function replace_image(UploadedFile $newFile, string $oldPath, string $directory = 'uplods_imges', string $disk = 'public'): string
{
if($oldPath){
    return $this->delete_image($oldPath,$disk);
}
 return $this->updload_file($newFile,$directory,$disk);
}


public function delete_image($path,$disk='public')
{
  if($path&&Storage::disk($disk)->exists($disk))
{ 
    return Storage::disk($disk)->delete($path);
     }
     return false;
}
}