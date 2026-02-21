<?php
namespace App\Http\Requests\Table;
use Illuminate\Foundation\Http\FormRequest;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        $storeId = $this->user()->store_id;
        return [
            'number'   => ['required', 'string', 'max:20', "unique:tables,number,NULL,id,store_id,{$storeId}"],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'section'  => ['nullable', 'string', 'max:50'],
        ];
    }
    public function messages(): array { return ['number.unique' => 'Nomor meja sudah digunakan.']; }
}
