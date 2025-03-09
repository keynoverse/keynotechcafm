<?php

namespace App\Http\Requests\AssetCategory;

class UpdateAssetCategoryRequest extends CreateAssetCategoryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Make all fields optional for update
        foreach ($rules as $field => $rule) {
            $rules[$field] = str_replace('required|', '', $rule);
        }
        
        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'id.required' => 'The category ID is required.',
            'id.exists' => 'The selected category does not exist.'
        ]);
    }
} 