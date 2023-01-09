<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id',
        'requirement'
    ];

    public function scholarships()
    {
        return $this->belongsTo(Scholarship::class, 'scholarship_id');
    }
}
