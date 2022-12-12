<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'scholar_id',
        'file_path'
    ];

    public function document_histories()
    {
        return $this->hasMany(DocumentHistory::class);
    }
}

