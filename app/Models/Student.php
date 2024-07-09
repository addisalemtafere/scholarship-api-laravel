<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    use HasFactory;

    protected $fillable = [
        'user_id', 'country', 'university', 'year_of_study', 'gpa',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
