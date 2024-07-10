<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class ScholarshipController extends Controller
{

    /**
     * Display a listing of the scholarships.
     *
     * @OA\Get(
     *     path="/api/scholarships",
     *     tags={"Scholarships"},
     *     summary="Get all scholarships",
     *     @OA\Response(
     *         response=200,
     *         description="List of scholarships",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(
     *                     property="eligibility",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="criteria", type="string"),
     *                         @OA\Property(property="minimum_gpa", type="number"),
     *                         @OA\Property(property="country", type="string"),
     *                         @OA\Property(property="experience", type="integer"),
     *                         @OA\Property(property="english_proficiency", type="boolean")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $scholarships = Scholarship::with('eligibility')->get();
        return response()->json($scholarships, 200);
    }

    /**
     * Store a newly created scholarship with eligibility criteria.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/scholarships",
     *     tags={"Scholarships"},
     *     summary="Create a new scholarship",
     *     description="Create a new scholarship with eligibility criteria",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "eligibility"},
     *             @OA\Property(property="name", type="string", example="Scholarship Name"),
     *             @OA\Property(property="description", type="string", example="Scholarship Description"),
     *             @OA\Property(
     *                 property="eligibility",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="criteria", type="string", example="Eligibility Criteria"),
     *                     @OA\Property(property="minimum_gpa", type="number", example=3.5),
     *                     @OA\Property(property="country", type="string", example="Country Name"),
     *                     @OA\Property(property="experience", type="integer", example=2),
     *                     @OA\Property(property="english_proficiency", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Scholarship created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(
     *                 property="eligibility",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="criteria", type="string"),
     *                     @OA\Property(property="minimum_gpa", type="number"),
     *                     @OA\Property(property="country", type="string"),
     *                     @OA\Property(property="experience", type="integer"),
     *                     @OA\Property(property="english_proficiency", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'eligibility' => 'required|array',
            'eligibility.*.criteria' => 'required|string',
            'eligibility.*.minimum_gpa' => 'nullable|numeric|between:0,4.0',
            'eligibility.*.country' => 'nullable|string|max:255',
            'eligibility.*.experience' => 'nullable|integer',
            'eligibility.*.english_proficiency' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 422);
        }

        try {
            $scholarship = Scholarship::create($validator->validated());

            foreach ($request->eligibility as $eligibility) {
                $scholarship->eligibility()->create($eligibility);
            }

            return $this->apiResponse($scholarship->load('eligibility'), null, 201);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified scholarship.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/scholarships/{id}",
     *     tags={"Scholarships"},
     *     summary="Get a scholarship by ID",
     *     description="Returns a specific scholarship by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(
     *                 property="eligibility",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="criteria", type="string"),
     *                     @OA\Property(property="minimum_gpa", type="number"),
     *                     @OA\Property(property="country", type="string"),
     *                     @OA\Property(property="experience", type="integer"),
     *                     @OA\Property(property="english_proficiency", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Scholarship not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $scholarship = Scholarship::with('eligibility')->find($id);

        if (!$scholarship) {
            return $this->apiResponse(null, 'Scholarship not found', 404);
        }

        return $this->apiResponse($scholarship, null, 200);
    }

    /**
     * Update the specified scholarship in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path="/api/scholarships/{id}",
     *     tags={"Scholarships"},
     *     summary="Update a scholarship",
     *     description="Update a scholarship with new details and eligibility criteria",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "eligibility"},
     *             @OA\Property(property="name", type="string", example="Updated Scholarship Name"),
     *             @OA\Property(property="description", type="string", example="Updated Scholarship Description"),
     *             @OA\Property(
     *                 property="eligibility",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="criteria", type="string", example="Updated Eligibility Criteria"),
     *                     @OA\Property(property="minimum_gpa", type="number", example=3.7),
     *                     @OA\Property(property="country", type="string", example="Updated Country Name"),
     *                     @OA\Property(property="experience", type="integer", example=3),
     *                     @OA\Property(property="english_proficiency", type="boolean", example=false)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Scholarship updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(
     *                 property="eligibility",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="criteria", type="string"),
     *                     @OA\Property(property="minimum_gpa", type="number"),
     *                     @OA\Property(property="country", type="string"),
     *                     @OA\Property(property="experience", type="integer"),
     *                     @OA\Property(property="english_proficiency", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Scholarship not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'eligibility' => 'required|array',
            'eligibility.*.criteria' => 'required|string',
            'eligibility.*.minimum_gpa' => 'nullable|numeric|between:0,4.0',
            'eligibility.*.country' => 'nullable|string|max:255',
            'eligibility.*.experience' => 'nullable|integer',
            'eligibility.*.english_proficiency' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 422);
        }

        try {
            $scholarship = Scholarship::findOrFail($id);
            $scholarship->update($validator->validated());

            $scholarship->eligibility()->delete(); // Remove existing eligibility criteria
            foreach ($request->eligibility as $eligibility) {
                $scholarship->eligibility()->create($eligibility);
            }

            return $this->apiResponse($scholarship->load('eligibility'), null, 200);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified scholarship from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path="/api/scholarships/{id}",
     *     tags={"Scholarships"},
     *     summary="Delete a scholarship",
     *     description="Delete a specific scholarship by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Scholarship deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Scholarship not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $scholarship = Scholarship::findOrFail($id);
            $scholarship->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return $this->apiResponse(null, 'Scholarship not found', 404);
        }
    }

    /**
     * Format API response.
     *
     * @param mixed $data
     * @param mixed $error
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    private function apiResponse($data = null, $error = null, $statusCode = 200)
    {
        $response = [
            'data' => $data,
            'error' => $error,
            'status' => $statusCode
        ];

        return response()->json($response, $statusCode);
    }
}
