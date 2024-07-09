<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use HasFactory;

    protected $table = 'scholarship';
    protected $fillable = ['name', 'description', 'deadline'];

    public function Eligibility()
    {
        return $this->hasMany(Eligibility::class);
    }
}
