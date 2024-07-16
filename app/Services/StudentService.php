<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StudentService
{
    public function getAllStudents()
    {
        return Student::all();
    }

    public function getStudentById($id)
    {
        return User::all()->find($id);
    }

    public function updateStudent($id, array $data): User
    {
        $user = User::where('role', 'student')->find($id);

        if (!$user) {
            throw new \Exception('Student not found');
        }

        // Validate and update user data
        $this->validateUpdateData($data, $id);

        $user->fill($data);
        $user->save();

        return $user;
    }

    public function deleteStudent($id)
    {
        $user = User::where('role', 'student')->find($id);

        if (!$user) {
            throw new \Exception('Student not found');
        }

        $user->delete();

        return true;
    }

    protected function validateUpdateData(array $data, $id): void
    {
        // Validation rules for update
        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'country' => 'sometimes|string|max:255',
            'university' => 'sometimes|string|max:255',
            'year_of_study' => 'sometimes|integer|min:1',
            'gpa' => 'sometimes|numeric|min:0|max:4',
        ];

        // Validate data
        validator()->validate($data, $rules);
    }
}
