<?php

namespace App\DTOs\Api;

class CategoryMealsData
{
    public function __construct(
        public readonly ?bool $featured = null,
        public readonly ?int $subcategoryId = null,
        public readonly ?bool $inStock = null,
        public readonly string $sortBy = 'created_at',
        public readonly string $sortOrder = 'desc',
        public readonly int $perPage = 15,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            featured: $data['featured'] ?? null,
            subcategoryId: $data['subcategory_id'] ?? null,
            inStock: $data['in_stock'] ?? null,
            sortBy: $data['sort_by'] ?? 'created_at',
            sortOrder: $data['sort_order'] ?? 'desc',
            perPage: $data['per_page'] ?? 15,

        );
    }
}
