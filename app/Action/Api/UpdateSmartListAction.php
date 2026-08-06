<?php

namespace App\Action\Api;

use App\Models\SmartList;
use Illuminate\Http\Request;

class UpdateSmartListAction
{
    public function execute($user, string $id, array $data, Request $request): SmartList
    {
        $smartList = SmartList::where('user_id', $user->id)->findOrFail($id);

        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/smart-lists'), $imageName);
            $data['image'] = $imageName;
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        return $smartList;
    }
}