<?php

namespace App\Http\Requests;

use App\Support\MarketTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;

class StoreWatchlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'market_type' => ['required', 'string', 'max:50', 'in:'.implode(',', MarketTypeRegistry::values())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
