<?php

namespace App\Actions\Api\SmartLists;

use App\Models\SmartList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateSmartListAction
{
    public function execute(SmartList $smartList, array $data, ?UploadedFile $image = null): SmartList
    {
        return DB::transaction(function () use ($smartList, $data, $image) {
            $mealIds = $data['meal_ids'] ?? null;
            unset($data['meal_ids']);

            if (array_key_exists('description', $data) && $data['description'] === null) {
                $data['description'] = '';
            }

            if ($image) {
                $data['image'] = $this->storeImage($image);
            }

            $smartList->update($data);

            if ($mealIds !== null) {
                $smartList->meals()->sync($mealIds);
            }

            return $smartList->fresh()->load('meals');
        });
    }

    private function storeImage(UploadedFile $image): string
    {
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/smart-lists'), $imageName);

        return $imageName;
    }
}
