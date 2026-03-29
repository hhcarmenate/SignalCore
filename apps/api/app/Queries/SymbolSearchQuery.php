<?php

namespace App\Queries;

use App\Models\Symbol;
use Illuminate\Database\Eloquent\Collection;

class SymbolSearchQuery
{
    public function execute(array $filters = []): Collection
    {
        $search = strtoupper(trim((string) ($filters['search'] ?? '')));
        $assetType = $filters['asset_type'] ?? null;
        $status = $filters['status'] ?? null;
        $limit = max(1, min((int) ($filters['limit'] ?? 10), 25));

        $query = Symbol::query()
            ->select([
                'id',
                'symbol',
                'name',
                'asset_type',
                'exchange',
                'status',
                'market',
            ])
            ->where('market', 'us_equities');

        if ($assetType !== null) {
            $query->where('asset_type', $assetType);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $escapedSearch = addcslashes($search, '%_');
            $prefixSearch = $escapedSearch.'%';
            $containsSearch = '%'.$escapedSearch.'%';

            $query->where(function ($builder) use ($search, $prefixSearch, $containsSearch) {
                $builder
                    ->whereRaw('UPPER(symbol) LIKE ?', [$prefixSearch])
                    ->orWhereRaw('UPPER(name) LIKE ?', [$containsSearch]);
            });

            $query->orderByRaw(
                "case
                    when UPPER(symbol) = ? then 0
                    when UPPER(symbol) like ? then 1
                    when UPPER(name) like ? then 2
                    else 3
                end",
                [$search, $prefixSearch, $containsSearch]
            );
        }

        return $query
            ->orderByDesc('status')
            ->orderBy('symbol')
            ->limit($limit)
            ->get();
    }
}
