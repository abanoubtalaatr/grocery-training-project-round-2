<?php

namespace App\Actions\Api\SmartList;

use App\Models\SmartList;
use App\Traits\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class StoreSmartListAction
{
    use Media;

    public function run(Request $request): SmartList
    {
        $data = $request->validated();

        $data['user_id'] = Auth::id();
        $data['description'] = $data['description'] ?? '';

        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadPhoto(
                $request->file('image'),
                'smart-lists'
            );
        }

        $smartList = SmartList::create($data);

        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        return $smartList->load('meals');
    }
}