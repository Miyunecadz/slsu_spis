<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concern extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholar_id',
        'details'
    ];
    
    public function replies()
    {
        return $this->hasMany(ConcernReply::class);
    }

    public function scholars()
    {
        return $this->belongsTo(Scholar::class,'scholar_id');
    }
}
