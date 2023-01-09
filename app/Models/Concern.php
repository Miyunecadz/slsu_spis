<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Concern extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholar_history_id',
        'details'
    ];
    
    public function replies()
    {
        return $this->hasMany(ConcernReply::class);
    }

    public function scholars()
    {
        return $this->hasOneThrough(Scholar::class,ScholarHistory::class,'id', 'scholar_id', 'scholar_history_id');
    }

    public function scholarHistories()
    {
        return $this->BelongsTo(ScholarHistory::class, 'scholar_history_id', 'id');
    }
}
