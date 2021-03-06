<?php

namespace JocelimJr\LumenDTSS\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface DTSSRepositoryInterface
{
    public function simple(Request $request, string $modelClass, array $columns, array $extraWhere = []): array;
    public function byQueryBuilder(Request $request, Builder $modelClass, array $columns): array;
}