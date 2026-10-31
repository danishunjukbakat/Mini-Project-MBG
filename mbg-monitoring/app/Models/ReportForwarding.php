<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportForwarding extends Model
{
    protected $fillable = [
        'report_id',
        'recipient_category',
        'recipient_name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'forwarded_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }
}
