<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\User;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Throwable;


class UserController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"auth"},
     *     summary="Register a new user",
     *     description="Registers a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User Registered Successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="status", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Registration failed"
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
            return response()->json([
                "message" => "User Registered Successfully",
                "data" => $user,
                "status" => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"auth"},
     *     summary="Login a user",
     *     description="Logs in a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        try {
            $inputs = $request->only('email', 'password');

            if (!$token = auth()->attempt($inputs)) {
                return response()->json([
                    'message' => 'You Are Unauthenticated',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $this->respondWithToken($token);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    protected function respondWithToken($token)
    {
        $ttl = config('jwt.ttl') * 60;
        return response()->json([
            'user' => auth()->user(),
            'access_token' => $token
        ]);
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"auth"},
     *     summary="Get authenticated user details",
     *     description="Returns the authenticated user's details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user details",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function me()
    {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json(["message" => "You Are Unauthenticated"], Response::HTTP_UNAUTHORIZED);
            }
            return response()->json($authUser);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch user details',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"auth"},
     *     summary="Logout a user",
     *     description="Logs out the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User Logged Out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to logout, token invalid"
     *     )
     * )
     */
    public function logout()
    {
        try {
            if (!auth()->check()) {
                return response()->json(["message" => "You Are Unauthenticated"], Response::HTTP_UNAUTHORIZED);
            }

            auth()->logout();
            return response()->json(["message" => "User Logged Out"], Response::HTTP_OK);
        } catch (JWTException $e) {
            return response()->json(["message" => "Failed to logout, token invalid"], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
