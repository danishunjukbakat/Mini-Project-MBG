<?php

namespace App\Observers;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportObserver
{
    public function updated(Report $report): void
    {
        // Hanya mencatat jika status berubah.
        if (!$report->wasChanged('status')) {
            return;
        }

        // Ambil nilai sebelum dan sesudah perubahan.
        $previous = $report->getPrevious();

        Log::info('MBG report status changed', [
            'report_id' => $report->id,
            'old_status' => $previous['status'] ?? null,
            'new_status' => $report->status,
            'user_id' => Auth::id(),
            'changed_at' => now()->toIso8601String(),
        ]);
    }
}
