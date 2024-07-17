<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
/**
 * @OA\Schema(
 *     schema="UpdateProductRequest",
 *     type="object",
 *     title="UpdateProductRequest",
 *     description="Update Product Request",
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
class UpdateProductRequest extends FormRequest
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
        $method = $this->method();
        if($method=='PUT'){
            return [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'price' => ['required', 'numeric', 'min:0'],
            ];
        }
        else{
            return [
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'price' => ['sometimes', 'numeric', 'min:0'],
            ];
        }
        
    }
}
