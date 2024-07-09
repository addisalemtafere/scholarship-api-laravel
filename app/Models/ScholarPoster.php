<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarPoster extends Model
{
    use HasFactory;
    use HasFactory;

    protected $fillable = [
        'user_id', 'organization', 'website',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
