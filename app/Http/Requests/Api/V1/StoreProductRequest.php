<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreProductRequest",
 *     type="object",
 *     title="StoreProductRequest",
 *     description="Store Product Request",
 *     required={"name", "description", "price"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the product"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the product"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Price of the product"
 *     )
 * )
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
    
}
