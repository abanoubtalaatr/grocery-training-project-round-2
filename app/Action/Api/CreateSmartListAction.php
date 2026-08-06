<?php

namespace App\Action\Api;

use App\Models\SmartList;
use Illuminate\Http\Request;

class CreateSmartListAction
{
    public function execute($user, array $data, Request $request): SmartList
    {
        $data['user_id'] = $user->id;
        $data['description'] = $data['description'] ?? '';

        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/smart-lists'), $imageName);
            $data['image'] = $imageName;
        }

        $smartList = SmartList::create($data);

        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        return $smartList;
    }
}