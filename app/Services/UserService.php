<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function registerUser(array $data): User
    {
        $this->validateRegisterData($data);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign role based on request
        switch ($data['role']) {
            case 'student':
                $this->createStudentProfile($user, $data);
                break;
            case 'scholar_poster':
                $this->createScholarPosterProfile($user, $data);
                break;
            default:
                throw ValidationException::withMessages([
                    'role' => 'Invalid role specified.',
                ]);
        }

        return $user;
    }

    protected function validateRegisterData(array $data): void
    {
        // Validation rules
        $rules = [
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
        ];

        // Validate data
        validator()->validate($data, $rules);
    }

    protected function createStudentProfile(User $user, array $data): void
    {
        $user->student()->create([
            'country' => $data['country'],
            'university' => $data['university'],
            'year_of_study' => $data['year_of_study'],
            'gpa' => $data['gpa'],
        ]);
    }

    protected function createScholarPosterProfile(User $user, array $data): void
    {
        $user->scholarPoster()->create([
            'organization' => $data['organization'],
            'website' => $data['website'],
        ]);
    }
}
