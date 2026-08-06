<?php

namespace App\Action\Api;

use App\Models\SmartList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreateSmartListAction
{
    public function execute($user, array $data, ?UploadedFile $image = null): SmartList
    {
        $data['user_id'] = $user->id;
        $data['description'] = $data['description'] ?? '';

        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);

        if ($image) {
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('smart-lists', $imageName, 'public');
            $data['image'] = $path;
        }

        $smartList = SmartList::create($data);

        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        return $smartList;
    }
}
