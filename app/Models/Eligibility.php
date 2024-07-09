<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eligibility extends Model
{
    use HasFactory;

    protected $table='eligibility';
    protected $fillable = [
        'criteria',
        'minimum_gpa',
        'country',
        'experience',
        'english_proficiency'
    ];

}
