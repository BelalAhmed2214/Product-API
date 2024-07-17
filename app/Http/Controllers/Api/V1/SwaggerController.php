<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API Documentation",
 *      description="API Documentation for User API",
 *      @OA\Contact(
 *          email="your-email@example.com"
 *      )
 * ),
 * @OA\Server(
 *      url="http://localhost:8000/api",
 *      description="Local Server"
 * ),
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 * ),
 * @OA\SecurityRequirement(
 *      security={{"bearerAuth":{}}}
 * )
 */
class SwaggerController extends Controller
{
    //
}
