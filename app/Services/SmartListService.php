<?php

namespace App\Services;

use App\Models\SmartList;
use Illuminate\Http\Request;

class SmartListService
{
    public function store(SmartListRequest $request): SmartList
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;
        $data['description'] = $data['description'] ?? '';

        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request);
        }

        $smartList = SmartList::create($data);

        if (! empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        return $smartList->load('meals');
    }

    public function update(SmartList $smartList, array $data, Request $request): SmartList
    {
        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request);
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        return $smartList->load('meals');
    }

    public function uploadImage(Request $request): string
    {
        $image = $request->file('image');

        $imageName = time().'.'.$image->getClientOriginalExtension();

        $image->move(public_path('images/smart-lists'), $imageName);

        return $imageName;
    }
}
