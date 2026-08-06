<?php

namespace App\Actions\Api\SmartList;

use App\Models\SmartList;
use App\Traits\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateSmartListAction
{
    use Media;

    public function run(Request $request, int $id): SmartList
    {
        $smartList = SmartList::where('user_id', Auth::id())
            ->findOrFail($id);

        $data = $request->validated();

        if (
            array_key_exists('description', $data) &&
            $data['description'] === null
        ) {
            $data['description'] = '';
        }

        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {

            if ($smartList->image) {
                $this->deletePhoto($smartList->image);
            }

            $data['image'] = $this->uploadPhoto(
                $request->file('image'),
                'smart-lists'
            );
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        return $smartList->load('meals');
    }
}