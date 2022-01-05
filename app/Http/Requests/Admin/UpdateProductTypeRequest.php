<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update productTypes', ProductType::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'icon' => 'sometimes|file|max:1024',
            'name' => 'required',
            'description' => 'sometimes',
            
            'status' => [
                'required',
                Rule::in(['1', '0'])
            ],
        ];

        return $rules;
    }
}
