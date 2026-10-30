
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'distribution_id',
        'title',
        'incident_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }
}
