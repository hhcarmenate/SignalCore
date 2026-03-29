<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SymbolIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'asset_type' => ['nullable', 'string', Rule::in(['stock', 'etf'])],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
        ];
    }
}
