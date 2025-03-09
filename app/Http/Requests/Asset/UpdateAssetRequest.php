<?php

namespace App\Http\Requests\Asset;

use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdateAssetRequest",
 *     title="Update Asset Request",
 *     description="Request body for updating an existing asset",
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