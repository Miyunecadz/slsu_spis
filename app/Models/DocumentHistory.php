<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'status'
    ];

    public function documents()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
