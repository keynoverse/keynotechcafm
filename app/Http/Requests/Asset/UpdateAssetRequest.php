<?php

namespace App\Http\Requests\Asset;

use Illuminate\Validation\Rule;

class UpdateAssetRequest extends CreateAssetRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Make category_id and space_id optional for updates
        $rules['category_id'] = 'sometimes|exists:asset_categories,id';
        $rules['space_id'] = 'sometimes|exists:spaces,id';
        
        // Update unique rules to exclude current asset
        $rules['code'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('assets', 'code')->ignore($this->route('id'))
        ];
        
        $rules['serial_number'] = [
            'nullable',
            'string',
            'max:100',
            Rule::unique('assets', 'serial_number')->ignore($this->route('id'))
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.unique' => 'The provided asset code is already in use by another asset.',
            'serial_number.unique' => 'The provided serial number is already in use by another asset.'
        ]);
    }
} 