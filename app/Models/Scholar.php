<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scholar extends Model
{
    use HasFactory, SoftDeletes;

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

    public function concerns()
    {
        return $this->hasMany(Concern::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
