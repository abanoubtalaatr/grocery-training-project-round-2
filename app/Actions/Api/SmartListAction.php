<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\DTOs\Api\SmartListData;
use App\Models\SmartList;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SmartListAction
{
    public function handle(User $user, SmartListData $data, ?SmartList $smartList = null): SmartList
    {
        $imagePath = $smartList?->image;

        if ($data->image) {
            if ($smartList?->image && Storage::disk('public')->exists($smartList->image)) {
                Storage::disk('public')->delete($smartList->image);
            }

            $imagePath = $data->image?->store('smart-lists', 'public');
        }

        $attributes = [
            'name' => $data->name,
            'description' => $data->description ?? '',
            'category' => $data->category,
            'image' => $imagePath,
            'notify_on_price_drop' => $data->notifyOnPriceDrop,
            'notify_on_offers' => $data->notifyOnOffers
        ];

        if ($smartList) {
            $smartList->update($attributes);
        } else {
            $smartList = $user->smartLists()->create($attributes);
        }

        if ($data->mealIds !== null) {
            $smartList->meals()->sync($data->mealIds);
        }

        return $smartList->load('meals');
    }
}
