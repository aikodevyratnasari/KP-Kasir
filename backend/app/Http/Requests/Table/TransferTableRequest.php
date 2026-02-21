<?php
namespace App\Http\Requests\Table;
use Illuminate\Foundation\Http\FormRequest;

class TransferTableRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array { return ['table_id' => ['required', 'exists:tables,id']]; }
}
