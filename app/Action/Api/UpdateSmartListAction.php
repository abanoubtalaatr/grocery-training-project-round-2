<?php

namespace App\Action\Api;

use App\Models\SmartList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateSmartListAction
{
    public function execute($user, string $id, array $data, ?UploadedFile $image = null): SmartList
    {
        $smartList = SmartList::where('user_id', $user->id)->findOrFail($id);

        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($image) {
            if ($smartList->image && Storage::disk('public')->exists($smartList->image)) {
                Storage::disk('public')->delete($smartList->image);
            }

            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('smart-lists', $imageName, 'public');
            $data['image'] = $path;
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        return $smartList;
    }
}
