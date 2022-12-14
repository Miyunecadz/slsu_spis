<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'scholar_history_id',
        'document_for',
        'academic_year',
        'file_path'
    ];

    public function document_histories()
    {
        return $this->hasMany(DocumentHistory::class);
    }

    public function scholarHistories()
    {
        return $this->belongsTo(ScholarHistory::class, 'scholar_history_id', 'id');
    }

    public function scholars()
    {
        return $this->hasOneThrough(Scholar::class, ScholarHistory::class, 'scholar_id', 'id');
    }
}

