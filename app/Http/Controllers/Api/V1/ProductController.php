<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\SpecificProductResource;

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
     * @OA\Get(
     *     path="/products",
     *     tags={"products"},
     *     summary="Get list of products",
     *     description="Returns a list of products",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Product"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No products found"
     *     )
     * )
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
     * @OA\Post(
     *     path="/products",
     *     tags={"products"},
     *     summary="Create a new product",
     *     description="Creates a new product",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreProductRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Product creation failed"
     *     )
     * )
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
     * @OA\Get(
     *     path="/products/{id}",
     *     tags={"products"},
     *     summary="Get a product by ID",
     *     description="Returns a single product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function show(Product $product){
        return response()->json([
            'Product' => new SpecificProductResource($product)
        ], Response::HTTP_OK);    
    }

    /**
     * @OA\Put(
     *     path="/products/{id}",
     *     tags={"products"},
     *     summary="Update an existing product",
     *     description="Updates a product by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProductRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Product update failed"
     *     )
     * )
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
     * @OA\Delete(
     *     path="/products/{id}",
     *     tags={"products"},
     *     summary="Delete a product",
     *     description="Deletes a product by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Product deletion failed"
     *     )
     * )
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
