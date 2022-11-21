<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholar extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'id_number',
        'department',
        'course',
        'major',
        'year_level',
        'scholarship_id',
        'email'
    ];
}
