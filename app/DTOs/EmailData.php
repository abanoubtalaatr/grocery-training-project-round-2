<?php

namespace App\DTOs;

class EmailData
{
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $view,
        public readonly array $data = [],
        public readonly array $attachments = [],
    ) {}
}