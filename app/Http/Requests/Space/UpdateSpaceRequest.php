<?php

namespace App\Http\Requests\Space;

use Illuminate\Validation\Rule;

class UpdateSpaceRequest extends CreateSpaceRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Make floor_id optional for updates
        $rules['floor_id'] = 'sometimes|exists:floors,id';
        
        // Update unique rule for code to exclude current space
        $rules['code'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('spaces', 'code')->ignore($this->route('id'))
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'code.unique' => 'The provided space code is already in use by another space.'
        ]);
    }
} 