<?php
namespace App\Http\Requests\Product;
use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        return ['quantity' => ['required', 'integer', 'min:0'], 'notes' => ['nullable', 'string', 'max:255']];
    }
}
