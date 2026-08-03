<?php

declare(strict_types=1);

namespace App\DTOs\Api;

use Illuminate\Http\UploadedFile;

final readonly class SmartListData
{
    public function __construct(
        public string $name,
        public ?string $category = null,
        public ?string $description = null,
        public ?UploadedFile $image = null,
        public ?bool $notifyOnPriceDrop = true,
        public ?bool $notifyOnOffers = true,
        public ?array $mealIds = null,
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
            mealIds: $data['meal_ids'] ?? null,

        );
    }
}
