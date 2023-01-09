<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventIndividual extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'scholar_history_id',
        'scholar_id'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function scholarHistories()
    {
        return $this->belongsTo(ScholarHistory::class, 'id', 'scholar_history_id');
    }
}
