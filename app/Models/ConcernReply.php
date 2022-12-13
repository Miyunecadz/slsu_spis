<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConcernReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'concern_id',
        'user_id',
        'reply'
    ];


    public function concern()
    {
        return $this->belongsTo(Concern::class);
    }
}
