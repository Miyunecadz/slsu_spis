<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventIndividual extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'scholar_id'
    ];
}
