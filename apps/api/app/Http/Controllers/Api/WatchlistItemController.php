<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWatchlistItemRequest;
use App\Models\Symbol;
use App\Models\Watchlist;
use App\Models\WatchlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WatchlistItemController extends Controller
{
    public function store(StoreWatchlistItemRequest $request, Watchlist $watchlist): JsonResponse
    {
        $symbol = $request->filled('symbol_id')
            ? Symbol::query()->findOrFail($request->integer('symbol_id'))
            : $this->resolveSymbol($watchlist, $request->validated('symbol'));

        $exists = $watchlist->items()
            ->where('symbol_id', $symbol->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'symbol' => ['This symbol already exists in the selected watchlist.'],
            ]);
        }

        $item = WatchlistItem::create([
            'watchlist_id' => $watchlist->id,
            'symbol_id' => $symbol->id,
            'notes' => $request->input('notes'),
        ])->load('symbol');

        return response()->json([
            'data' => $item,
        ], 201);
    }

    public function destroy(Watchlist $watchlist, WatchlistItem $item): JsonResponse
    {
        abort_unless($item->watchlist_id === $watchlist->id, 404);

        $item->delete();

        return response()->json(status: 204);
    }

    private function resolveSymbol(Watchlist $watchlist, array $payload): Symbol
    {
        $ticker = strtoupper(trim($payload['symbol']));
        $provider = $payload['provider'] ?? 'manual';
        $providerSymbol = strtoupper(trim($payload['provider_symbol'] ?? $ticker));

        return Symbol::query()->updateOrCreate(
            [
                'market' => $watchlist->market_type,
                'symbol' => $ticker,
            ],
            [
                'asset_type' => $payload['asset_type'],
                'name' => $payload['name'] ?? $ticker,
                'exchange' => $payload['exchange'] ?? null,
                'status' => 'active',
                'currency' => $payload['currency'] ?? null,
                'provider' => $provider,
                'provider_symbol' => $providerSymbol,
                'metadata' => null,
            ],
        );
    }
}
