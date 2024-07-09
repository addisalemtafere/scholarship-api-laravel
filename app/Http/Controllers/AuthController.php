<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:student,scholar_poster',
            'country' => 'required_if:role,student|string|max:255',
            'university' => 'required_if:role,student|string|max:255',
            'year_of_study' => 'required_if:role,student|integer|min:1',
            'gpa' => 'required_if:role,student|numeric|min:0|max:4',
            'organization' => 'required_if:role,scholar_poster|string|max:255',
            'website' => 'required_if:role,scholar_poster|string|url|max:255',
        ]);

        if ($validator->fails()) {
            return $this->responseWithError($validator->errors()->first(), 422);
        }

        try {
            // Create the user using the UserService
            $user = $this->userService->registerUser($request->all());

            // Generate a Sanctum token
            $token = $user->createToken('authToken')->plainTextToken;

            return $this->responseWithTokenAndData($token, $user, 201);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500);
        }
    }

    /**
     * Authenticate user and issue a Sanctum token.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->responseWithError($validator->errors()->first(), 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->responseWithError('Invalid credentials', 401);
        }

        $user = Auth::user();

        // Generate Sanctum token for the authenticated user
        $token = $user->createToken('authToken')->plainTextToken;

        return $this->responseWithTokenAndData($token, $user, 200);
    }

    /**
     * Helper method to format success response with Sanctum token and user data.
     *
     * @param string $token
     * @param mixed $user
     * @param int $httpStatus
     * @return \Illuminate\Http\JsonResponse
     */
    protected function responseWithTokenAndData(string $token, $user, int $httpStatus = 200): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'error' => null,
            'status' => 'success',
        ], $httpStatus);
    }

    /**
     * Helper method to format error response.
     *
     * @param string $message
     * @param int $httpStatus
     * @return \Illuminate\Http\JsonResponse
     */
    protected function responseWithError(string $message, int $httpStatus): JsonResponse
    {
        return response()->json([
            'data' => null,
            'error' => $message,
            'status' => 'error',
        ], $httpStatus);
    }
}
