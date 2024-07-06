<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Resources\Api\V1\ProductResource;
use Illuminate\Http\Request;
use App\Services\V1\ProductService;
use App\Filters\V1\ProductFilter;
use Exception;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = ProductFilter::apply($request);
        $products = $this->productService->getAllProducts($filters);

        if ($products->isEmpty()) {
            return response()->json(["message" => "No products found", "status" => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
        }
        return response()->json(["products" => $products, "status" => Response::HTTP_OK], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            return response()->json([
                'product' => new ProductResource($product),
                'message' => 'Product created successfully',
                'status' => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Product creation failed',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $updatedProduct = $this->productService->updateProduct($product, $request->validated());

            if (!$updatedProduct) {
                return response()->json([
                    'message' => 'No changes detected in the product data',
                    'status' => Response::HTTP_OK
                ], Response::HTTP_OK);
            }

            return response()->json([
                'product' => new ProductResource($updatedProduct),
                'message' => 'Product updated successfully',
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Product update failed',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $this->productService->deleteProduct($product);
            return response()->json([
                'message' => 'Product deleted successfully',
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Product deletion failed',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
