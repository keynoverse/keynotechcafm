<?php

namespace App\Http\Requests\AssetCategory;

use App\Http\Requests\BaseFormRequest;

class CreateAssetCategoryRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:asset_categories,id',
            'status' => 'required|in:active,inactive',
            'metadata' => 'nullable|array'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.max' => 'The category name cannot exceed 255 characters.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'status.required' => 'The category status is required.',
            'status.in' => 'The category status must be either active or inactive.',
            'metadata.array' => 'The metadata must be a valid JSON object.'
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'category name',
            'description' => 'category description',
            'parent_id' => 'parent category',
            'status' => 'category status',
            'metadata' => 'metadata'
        ];
    }
} 