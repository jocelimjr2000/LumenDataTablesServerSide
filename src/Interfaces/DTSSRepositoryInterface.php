<?php

namespace JocelimJr\LumenDTSS\Interfaces;

use Illuminate\Http\Request;

interface DTSSRepositoryInterface
{
    public function simple(Request $request, string $modelClass, array $columns, array $extraWhere = []): array;
}