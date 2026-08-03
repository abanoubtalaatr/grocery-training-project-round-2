<?php

declare(strict_types=1);

namespace App\DTOs\Api;

use App\Models\SmartList;

final readonly class SmartListMealResult
{
    public function __construct(
        public SmartList $smartList,
        public bool $isNew,
    ) {}
}