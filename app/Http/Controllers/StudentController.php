<?php

namespace App\Http\Controllers;

use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;


class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * @OA\Get(
     *     path="/api/students",
     *     tags={"Students"},
     *     summary="Get all students",
     *     @OA\Response(
     *         response=200,
     *         description="List of students",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="user_id", type="integer"),
     *                     @OA\Property(property="country", type="string"),
     *                     @OA\Property(property="university", type="string"),
     *                     @OA\Property(property="year_of_study", type="integer"),
     *                     @OA\Property(property="gpa", type="number"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $students = $this->studentService->getAllStudents();

        return response()->json([
            'data' => $students,
            'error' => null,
            'status' => 'success',
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/students/{id}",
     *     tags={"Students"},
     *     summary="Get student by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Student ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="country", type="string"),
     *                 @OA\Property(property="university", type="string"),
     *                 @OA\Property(property="year_of_study", type="integer"),
     *                 @OA\Property(property="gpa", type="number"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $student = $this->studentService->getStudentById($id);

            return response()->json([
                'data' => $student,
                'error' => null,
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 'error',
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/students/{id}",
     *     tags={"Students"},
     *     summary="Update student",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Student ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"country", "university", "year_of_study", "gpa"},
     *             @OA\Property(property="country", type="string", example="United States"),
     *             @OA\Property(property="university", type="string", example="Example University"),
     *             @OA\Property(property="year_of_study", type="integer", example=3),
     *             @OA\Property(property="gpa", type="number", example=3.8)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="country", type="string"),
     *                 @OA\Property(property="university", type="string"),
     *                 @OA\Property(property="year_of_study", type="integer"),
     *                 @OA\Property(property="gpa", type="number"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $student = $this->studentService->updateStudent($id, $request->all());

            return response()->json([
                'data' => $student,
                'error' => null,
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 'error',
            ], 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/students/{id}",
     *     tags={"Students"},
     *     summary="Delete student",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Student ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->studentService->deleteStudent($id);

            return response()->json([
                'data' => null,
                'error' => null,
                'status' => 'success',
                'message' => 'Student deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 'error',
            ], 404);
        }
    }
}
