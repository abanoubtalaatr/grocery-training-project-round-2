<?php

namespace App\Actions\Api;

use App\DTOs\Api\CreateSmartListData;
use App\DTOs\Api\UpdateSmartListData;
use App\Models\SmartList;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SmartListAction
{
    public function create(User $user, CreateSmartListData $data): SmartList
    {
        $imagePath = null;

        if ($data->image instanceof UploadedFile) {
            $imagePath = $data->image->store('smart-lists', 'public');
        }

        try {
            return DB::transaction(function () use ($user, $data, $imagePath) {
                $smartList = $user->smartLists()->create([
                    'name'                 => $data->name,
                    'category'             => $data->category,
                    'description'          => $data->description,
                    'image'                => $imagePath,
                    'notify_on_price_drop' => $data->notifyOnPriceDrop,
                    'notify_on_offers'     => $data->notifyOnOffers,
                ]);

                if (!empty($data->mealIds)) {
                    $smartList->meals()->sync($data->mealIds);
                }

                return $smartList->load('meals');
            });
        } catch (\Throwable $e) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $e;
        }
    }

    public function update(SmartList $smartList, UpdateSmartListData $data): SmartList
    {
        $attributes = $data->updatedFields;
        $oldImage = $smartList->image;
        $newUploadedPath = null;

        if (
            array_key_exists('image', $attributes) &&
            $attributes['image'] instanceof UploadedFile
        ) {
            $newUploadedPath = $attributes['image']->store(
                'smart-lists',
                'public'
            );

            $attributes['image'] = $newUploadedPath;
        }

        try {
            $updatedList = DB::transaction(function () use (
                $smartList,
                $data,
                $attributes
            ) {
                if (!empty($attributes)) {
                    $smartList->update($attributes);
                }

                if ($data->mealIds !== null) {
                    $smartList->meals()->sync($data->mealIds);
                }

                return $smartList->load('meals');
            });

            if (
                array_key_exists('image', $data->updatedFields) &&
                $oldImage &&
                $oldImage !== $smartList->image &&
                Storage::disk('public')->exists($oldImage)
            ) {
                Storage::disk('public')->delete($oldImage);
            }

            return $updatedList;
        } catch (\Throwable $e) {
            if (
                $newUploadedPath &&
                Storage::disk('public')->exists($newUploadedPath)
            ) {
                Storage::disk('public')->delete($newUploadedPath);
            }

            throw $e;
        }
    }

    public function delete(SmartList $smartList): void
    {
        $imageToDelete = $smartList->image;

        DB::transaction(function () use ($smartList) {
            $smartList->delete();
        });

        if (
            $imageToDelete &&
            Storage::disk('public')->exists($imageToDelete)
        ) {
            Storage::disk('public')->delete($imageToDelete);
        }
    }
}
