<?php

namespace App\Actions\Api\SmartLists;

use App\Models\SmartList;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StoreSmartListAction
{
    public function execute(User $user, array $data, ?UploadedFile $image = null): SmartList
    {
        return DB::transaction(function () use ($user, $data, $image) {
            $mealIds = $data['meal_ids'] ?? [];
            unset($data['meal_ids']);

            $data['user_id'] = $user->id;
            $data['description'] = $data['description'] ?? '';

            if ($image) {
                $data['image'] = $this->storeImage($image);
            }

            $smartList = SmartList::create($data);

            if ($mealIds !== []) {
                $smartList->meals()->attach($mealIds);
            }

            return $smartList->load('meals');
        });
    }

    private function storeImage(UploadedFile $image): string
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/smart-lists'), $imageName);

        return $imageName;
    }
}
