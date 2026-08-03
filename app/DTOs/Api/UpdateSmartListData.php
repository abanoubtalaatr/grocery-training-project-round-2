<?php

namespace App\DTOs\Api;

final readonly class UpdateSmartListData
{
    public function __construct(
        public readonly array $updatedFields,
        public readonly ?array $mealIds = null
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            updatedFields: collect($data)->except('meal_ids')->all(),
            mealIds: array_key_exists('meal_ids', $data) ? $data['meal_ids'] : null
        );
    }
}