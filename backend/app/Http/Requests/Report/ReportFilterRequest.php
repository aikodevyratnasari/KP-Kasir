<?php
namespace App\Http\Requests\Report;
use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool { return in_array($this->user()->role->slug, ['admin', 'manager']); }
    public function rules(): array
    {
        return ['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'period' => ['nullable', 'in:daily,weekly,monthly,yearly']];
    }
}
