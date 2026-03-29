<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SymbolIndexRequest;
use App\Queries\SymbolSearchQuery;
use Illuminate\Http\JsonResponse;

class SymbolController extends Controller
{
    public function __construct(private readonly SymbolSearchQuery $symbolSearchQuery)
    {
    }

    public function index(SymbolIndexRequest $request): JsonResponse
    {
        $symbols = $this->symbolSearchQuery->execute($request->validated());

        return response()->json([
            'data' => $symbols,
        ]);
    }
}
