<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
    ];

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }
}
