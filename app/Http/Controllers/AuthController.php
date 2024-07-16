<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use App\Models\User;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "role"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255),
     *             @OA\Property(property="password", type="string", minLength=8),
     *             @OA\Property(property="role", type="string", enum={"student", "scholar_poster"}),
     *             @OA\Property(property="country", type="string", maxLength=255),
     *             @OA\Property(property="university", type="string", maxLength=255),
     *             @OA\Property(property="year_of_study", type="integer", minimum=1),
     *             @OA\Property(property="gpa", type="number", minimum=0, maximum=4),
     *             @OA\Property(property="organization", type="string", maxLength=255),
     *             @OA\Property(property="website", type="string", format="url", maxLength=255),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="string", enum={"error"})
     *         )
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:student,scholar_poster',
            'country' => 'required_if:role,student|string|max:255',
            'university' => 'required_if:role,student|string|max:255',
            'year_of_study' => 'required_if:role,student|integer|min:1',
            'gpa' => 'required_if:role,student|numeric|min:0|max:4',
            'organization' => 'required_if:role,scholar_poster|string|max:255',
            'website' => 'required_if:role,scholar_poster|string|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'status' => 'error',
            ], 422);
        }

        try {
            // Create the user using the UserService
            $user = $this->userService->registerUser($request->all());

            // Generate a Sanctum token
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
                'error' => null,
                'status' => 'success',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Authenticate user and issue a Sanctum token.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Authenticate user and issue a Sanctum token",
     *     description="Authenticate a user with email and password and issue a Sanctum token upon successful login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="sanctum_token_here")
     *             ),
     *             @OA\Property(property="error", type="null"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid credentials"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'status' => 'error',
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'error' => 'Invalid credentials',
                'status' => 'error',
            ], 401);
        }

        $user = Auth::user();

        // Generate Sanctum token for the authenticated user
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'token' => $token,
            ],
            'error' => null,
            'status' => 'success',
        ], 200);
    }
}
