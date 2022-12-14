<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use HasFactory;

    //added fillable
    protected $fillable = [
        'scholarship_name',
        'scholarship_detail',
    ];

    public function scholars()
    {
        return $this->hasMany(Scholar::class);
    }
}
