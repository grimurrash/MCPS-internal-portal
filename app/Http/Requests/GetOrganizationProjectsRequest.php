<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetOrganizationProjectsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sort_by' => ['sometimes', 'string'],
            'sort_desc' => ['sometimes', 'string'],
            'start_date' => ['sometimes','string'],
            'end_date' => ['sometimes','string']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
