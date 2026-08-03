<?php

namespace App\Traits;
trait media
{
    public function uploadPhoto($img,$dir): string
    {
        $imgName = uniqid() . $img->getClientOriginalExtension();
        $img->move(public_path('images/'.$dir), $imgName);
        return $imgName;
    }
    public function deletePhoto($imgPath): bool
    {
        if (file_exists($imgPath)) {
            unlink($imgPath);
            return true;
        }
        return false;
    }
}
