<?php

namespace App\Services\V1;

use App\Models\Product;
use App\Http\Resources\Api\V1\ProductResource;

class ProductService
{
    public function getAllProducts($filters)
    {
        $query = Product::query();

        // Apply filters
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply sorting
        if (!empty($filters['sort_by'])) {
            $sortField = $filters['sort_by'];
            $sortDirection = $filters['sort_direction'] ?? 'asc';

            if (in_array($sortField, ['name', 'price']) && in_array($sortDirection, ['asc', 'desc'])) {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->orderBy('id', 'asc'); // Default sorting
        }

        return ProductResource::collection($query->paginate());
    }

    public function createProduct($validatedData)
    {
        return Product::create($validatedData);
    }

    public function updateProduct(Product $product, $validatedData)
    {
        if (
            $product->name === $validatedData['name'] &&
            $product->description === $validatedData['description'] &&
            $product->price == $validatedData['price']
        ) {
            return false;
        }

        $product->update($validatedData);
        return $product;
    }

    public function deleteProduct(Product $product)
    {
        $product->delete();
    }
}
