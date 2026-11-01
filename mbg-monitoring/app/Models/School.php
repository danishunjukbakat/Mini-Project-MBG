<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
