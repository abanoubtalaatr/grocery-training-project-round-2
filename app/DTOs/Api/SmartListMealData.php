<?php

declare(strict_types=1);

namespace App\DTOs\Api;


final readonly class SmartListMealData
{
    public function __construct(public int $mealId) {}

    public static function fromValidated(array $data): self
    {
        return new self(mealId: $data['meal_id']);
    }
}
