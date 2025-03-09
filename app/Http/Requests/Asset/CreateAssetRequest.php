<?php

namespace App\Http\Requests\Asset;

use App\Http\Requests\BaseFormRequest;

/**
 * @OA\Schema(
 *     schema="CreateAssetRequest",
 *     title="Create Asset Request",
 *     description="Request body for creating a new asset",
 *     required={"category_id", "space_id", "name", "status", "condition", "criticality"},
 *     @OA\Property(property="category_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="space_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Office Chair"),
 *     @OA\Property(property="code", type="string", example="AST-001"),
 *     @OA\Property(property="description", type="string", example="Ergonomic office chair"),
 *     @OA\Property(property="model", type="string", example="XYZ-123"),
 *     @OA\Property(property="manufacturer", type="string", example="ABC Corp"),
 *     @OA\Property(property="serial_number", type="string", example="SN123456"),
 *     @OA\Property(property="purchase_date", type="string", format="date", example="2023-01-01"),
 *     @OA\Property(property="purchase_cost", type="number", format="float", example=299.99),
 *     @OA\Property(property="warranty_expiry", type="string", format="date", example="2024-01-01"),
 *     @OA\Property(property="maintenance_frequency", type="integer", example=30),
 *     @OA\Property(property="maintenance_unit", type="string", enum={"days", "weeks", "months", "years"}, example="days"),
 *     @OA\Property(property="next_maintenance_date", type="string", format="date", example="2023-12-31"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "maintenance", "retired", "storage"}, example="active"),
 *     @OA\Property(property="condition", type="string", enum={"excellent", "good", "fair", "poor"}, example="excellent"),
 *     @OA\Property(property="criticality", type="string", enum={"high", "medium", "low"}, example="medium"),
 *     @OA\Property(
 *         property="metadata",
 *         type="object",
 *         example={"color": "black", "material": "leather", "warranty_details": "3 years limited warranty"}
 *     )
 * )
 */
class CreateAssetRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:asset_categories,id',
            'space_id' => 'required|exists:spaces,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:assets,code',
            'description' => 'nullable|string',
            'model' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100|unique:assets,serial_number',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date|after_or_equal:purchase_date',
            'maintenance_frequency' => 'nullable|integer|min:1',
            'maintenance_unit' => 'nullable|string|in:days,weeks,months,years',
            'next_maintenance_date' => 'nullable|date|after:today',
            'status' => 'required|string|in:active,inactive,maintenance,retired,storage',
            'condition' => 'required|string|in:excellent,good,fair,poor',
            'criticality' => 'required|string|in:high,medium,low',
            'metadata' => 'nullable|json'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Asset category is required.',
            'category_id.exists' => 'Selected asset category does not exist.',
            'space_id.required' => 'Space assignment is required.',
            'space_id.exists' => 'Selected space does not exist.',
            'name.required' => 'Asset name is required.',
            'name.max' => 'Asset name cannot exceed 255 characters.',
            'code.unique' => 'Asset code must be unique.',
            'code.max' => 'Asset code cannot exceed 50 characters.',
            'model.max' => 'Model cannot exceed 100 characters.',
            'manufacturer.max' => 'Manufacturer cannot exceed 100 characters.',
            'serial_number.unique' => 'Serial number must be unique.',
            'serial_number.max' => 'Serial number cannot exceed 100 characters.',
            'purchase_date.date' => 'Purchase date must be a valid date.',
            'purchase_cost.numeric' => 'Purchase cost must be a number.',
            'purchase_cost.min' => 'Purchase cost cannot be negative.',
            'warranty_expiry.date' => 'Warranty expiry must be a valid date.',
            'warranty_expiry.after_or_equal' => 'Warranty expiry date must be after or equal to purchase date.',
            'maintenance_frequency.integer' => 'Maintenance frequency must be an integer.',
            'maintenance_frequency.min' => 'Maintenance frequency must be at least 1.',
            'maintenance_unit.in' => 'Invalid maintenance unit provided.',
            'next_maintenance_date.date' => 'Next maintenance date must be a valid date.',
            'next_maintenance_date.after' => 'Next maintenance date must be a future date.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status provided.',
            'condition.required' => 'Condition is required.',
            'condition.in' => 'Invalid condition provided.',
            'criticality.required' => 'Criticality is required.',
            'criticality.in' => 'Invalid criticality level provided.',
            'metadata.json' => 'Metadata must be a valid JSON string.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'asset category',
            'space_id' => 'space',
            'name' => 'asset name',
            'code' => 'asset code',
            'description' => 'description',
            'model' => 'model',
            'manufacturer' => 'manufacturer',
            'serial_number' => 'serial number',
            'purchase_date' => 'purchase date',
            'purchase_cost' => 'purchase cost',
            'warranty_expiry' => 'warranty expiry date',
            'maintenance_frequency' => 'maintenance frequency',
            'maintenance_unit' => 'maintenance unit',
            'next_maintenance_date' => 'next maintenance date',
            'status' => 'status',
            'condition' => 'condition',
            'criticality' => 'criticality level',
            'metadata' => 'metadata'
        ];
    }
} 