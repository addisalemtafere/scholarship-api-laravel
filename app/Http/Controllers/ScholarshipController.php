<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\Eligibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScholarshipController extends Controller
{
    public function index()
    {
        $scholarships = Scholarship::with('eligibility')->get();
        return $this->apiResponse($scholarships, null, 200);
    }

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

    public function show($id)
    {
        $scholarship = Scholarship::with('eligibility')->find($id);

        if (!$scholarship) {
            return $this->apiResponse(null, 'Scholarship not found', 404);
        }

        return $this->apiResponse($scholarship, null, 200);
    }

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

        $scholarship = Scholarship::find($id);

        if (!$scholarship) {
            return $this->apiResponse(null, 'Scholarship not found', 404);
        }

        try {
            $scholarship->update($validator->validated());

            $scholarship->eligibility()->delete();
            foreach ($request->eligibility as $eligibility) {
                $scholarship->eligibility()->create($eligibility);
            }

            return $this->apiResponse($scholarship->load('eligibility'), null, 200);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $scholarship = Scholarship::find($id);

        if (!$scholarship) {
            return $this->apiResponse(null, 'Scholarship not found', 404);
        }

        try {
            $scholarship->delete();
            return $this->apiResponse(null, 'Scholarship deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 500);
        }
    }

    private function apiResponse($data, $error, $status)
    {
        return response()->json([
            'data' => $data,
            'error' => $error,
            'status' => $status,
        ], $status);
    }
}
