<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholar_id',
        'academic_year_id',
        'academic_year',
        'semester',
        'qualified'
    ];


    public function events()
    {
        return $this->hasMany(Event::class, 'scholar_history_id', 'id');
    }

    public function concerns()
    {
        return $this->hasMany(Concern::class, 'scholar_history_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'scholar_history_id', 'id');
    }

    public function academicYears()
    {
        return $this->belongsTo(AcademicYear::class, 'id', 'academic_year_id');
    }

    public function scholars()
    {
        return $this->belongsTo(Scholar::class, 'scholar_id', 'id');
    }
}
