<?php

use App\Http\Controllers\Api\SymbolController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\WatchlistItemController;
use Illuminate\Support\Facades\Route;

Route::get('/symbols', [SymbolController::class, 'index']);

Route::get('/watchlists', [WatchlistController::class, 'index']);
Route::post('/watchlists', [WatchlistController::class, 'store']);
Route::get('/watchlists/{watchlist}', [WatchlistController::class, 'show']);
Route::delete('/watchlists/{watchlist}', [WatchlistController::class, 'destroy']);

Route::post('/watchlists/{watchlist}/items', [WatchlistItemController::class, 'store']);
Route::delete('/watchlists/{watchlist}/items/{item}', [WatchlistItemController::class, 'destroy']);
