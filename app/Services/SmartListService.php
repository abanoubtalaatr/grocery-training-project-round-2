<?php

namespace App\Services;

use App\Models\SmartList;
use Illuminate\Http\UploadedFile;

class SmartListService
{
    public function __construct(
        private ImageUploadService $imageUploader
    ) {}

  public function create(int $userId, array $data): SmartList
    {
        $mealIds = $this->extractMealIds($data);

        $data['user_id'] = $userId;
        $data['description'] ??= '';

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->imageUploader->upload($data['image'], 'smart-lists');
        }

        $smartList = SmartList::create($data);

        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        return $smartList->load('meals');
    }
     public function update(SmartList $smartList, array $data): SmartList
    {
        $mealIds = array_key_exists('meal_ids', $data) ? $data['meal_ids'] : null;
        unset($data['meal_ids']);

        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $this->imageUploader->delete($smartList->image, 'smart-lists');
            $data['image'] = $this->imageUploader->upload($data['image'], 'smart-lists');
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        return $smartList->load('meals');
    }
     public function delete(SmartList $smartList): void
    {
        $this->imageUploader->delete($smartList->image, 'smart-lists');
        $smartList->meals()->detach();
        $smartList->delete();
    }

     public function addMeal(SmartList $smartList, int $mealId): SmartList
    {
        $smartList->meals()->syncWithoutDetaching([$mealId]);
        return $smartList->load('meals');
    }
     public function removeMeal(SmartList $smartList, int $mealId): SmartList
    {
        $smartList->meals()->detach($mealId);
        return $smartList->load('meals');
    }
    private function extractMealIds(array &$data): array
    {
        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);
        return $mealIds;
    }


}