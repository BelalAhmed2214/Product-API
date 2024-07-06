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
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttl,
            'user' => auth()->user()
        ]);
    }

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
