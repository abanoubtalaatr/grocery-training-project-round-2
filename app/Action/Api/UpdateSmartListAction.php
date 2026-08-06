<?php

namespace App\Action\Api;

use App\Models\SmartList;
use Illuminate\Http\UploadedFile;

class UpdateSmartListAction
{
    public function execute($user, $id, array $data): SmartList
    {
        $smartList = SmartList::where('user_id', $user->id)->findOrFail($id);

        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
            $image = $data['image'];
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/smart-lists'), $imageName);
            $data['image'] = $imageName;
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        return $smartList->load('meals');
    }
}
