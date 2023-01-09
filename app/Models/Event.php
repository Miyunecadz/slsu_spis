<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'event_start',
        'event_end',
        'details',
        'academic_year'
    ];

    public function eventIndividual()
    {
        return $this->hasmany(EventIndividual::class);
    }
}
