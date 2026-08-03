<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface CategoryServiceInterface
{
    public function index();

    public function show(string $id);

    public function meals(string $id, Request $request): array;
}