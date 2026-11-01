<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distribution extends Model
{

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

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
