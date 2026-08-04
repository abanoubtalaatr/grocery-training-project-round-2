<?php

namespace App\Services;

use App\Models\SmartList;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\UploadedFile;

class SmartListService
{
    use HandlesImageUploads;

    protected const IMAGE_FOLDER = 'smart-lists';

    public function findForUser(int $userId, int $id, bool $withMeals = false): SmartList
    {
        $query = SmartList::where('user_id', $userId);

        if ($withMeals) {
            $query->with('meals');
        }

        return $query->findOrFail($id);
    }

    public function listForUser(int $userId)
    {
        return SmartList::where('user_id', $userId)->with('meals')->get();
    }

    public function create(array $data, int $userId, ?UploadedFile $image, array $mealIds = []): SmartList
    {
        $data['user_id'] = $userId;
        $data['description'] = $data['description'] ?? '';
        unset($data['meal_ids'], $data['image']);

        if ($image) {
            $data['image'] = $this->storeUploadedImage($image, self::IMAGE_FOLDER);
        }

        $smartList = SmartList::create($data);

        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        return $smartList->load('meals');
    }

    public function update(SmartList $smartList, array $data, ?UploadedFile $image, ?array $mealIds = null): SmartList
    {
        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }
        unset($data['meal_ids'], $data['image']);

        if ($image) {
            $data['image'] = $this->storeUploadedImage($image, self::IMAGE_FOLDER, $smartList->image);
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        return $smartList->load('meals');
    }

    public function delete(SmartList $smartList): void
    {
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
}