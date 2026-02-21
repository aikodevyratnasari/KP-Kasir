<?php
namespace App\Http\Requests\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        $storeId = $this->user()->store_id;
        $id      = $this->route('category');
        return [
            'name'        => ['required', 'string', 'max:100', "unique:categories,name,{$id},id,store_id,{$storeId},deleted_at,NULL"],
            'description' => ['nullable', 'string', 'max:500'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
}
