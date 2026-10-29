<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    protected $fillable = [
        'school_id',
        'code',
        'distribution_date',
        'meal_description',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
