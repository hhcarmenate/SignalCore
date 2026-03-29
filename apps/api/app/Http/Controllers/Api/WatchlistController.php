<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWatchlistRequest;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;

class WatchlistController extends Controller
{
    public function index(): JsonResponse
    {
        $watchlists = Watchlist::query()
            ->withCount('items')
            ->latest()
            ->get();

        return response()->json([
            'data' => $watchlists,
        ]);
    }

    public function store(StoreWatchlistRequest $request): JsonResponse
    {
        $watchlist = Watchlist::create([
            'name' => $request->string('name')->toString(),
            'description' => $request->input('description'),
            'market_type' => $request->string('market_type')->toString(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'data' => $watchlist->loadCount('items'),
        ], 201);
    }

    public function show(Watchlist $watchlist): JsonResponse
    {
        return response()->json([
            'data' => $watchlist->load([
                'items.symbol',
            ]),
        ]);
    }

    public function destroy(Watchlist $watchlist): JsonResponse
    {
        $watchlist->delete();

        return response()->json(status: 204);
    }
}
