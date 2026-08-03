<?php

namespace App\DTOs\Api;

final readonly class CreateSmartListData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $category = null,
        public readonly ?string $description = null,
        public readonly mixed $image = null, 
        public readonly bool $notifyOnPriceDrop = true, 
        public readonly bool $notifyOnOffers = true,   
        public readonly array $mealIds = []
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            name: $data['name'],
            category: $data['category'] ?? null,
            description: $data['description'] ?? null,
            image: $data['image'] ?? null,
            notifyOnPriceDrop: $data['notify_on_price_drop'] ?? true,
            notifyOnOffers: $data['notify_on_offers'] ?? true,
            mealIds: $data['meal_ids'] ?? []
        );
    }
}