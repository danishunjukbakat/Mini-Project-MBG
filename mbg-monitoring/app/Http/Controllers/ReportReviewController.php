<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportReviewController extends Controller
{
    // Memulai proses peninjauan.
    public function start(Report $report): RedirectResponse
    {
        DB::transaction(function () use ($report) {
            $lockedReport = Report::query()
                ->whereKey($report->id)
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('startReview', $lockedReport);

            $lockedReport->status = 'under_review';
            $lockedReport->save();
        });

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Peninjauan laporan dimulai.');
    }

    // Menyimpan hasil peninjauan.
    public function store(
        Request $request,
        Report $report
    ): RedirectResponse {
        Gate::authorize('review', $report);

        $validated = $request->validate([
            'notes' => [
                'required', 'string', 'min:5', 'max:2000'
            ],
            'needs_forwarding' => [
                'required', 'boolean'
            ],
        ]);

        DB::transaction(function () use ($request, $report, $validated) {
            $lockedReport = Report::query()
                ->whereKey($report->id)
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('review', $lockedReport);

            $needsForwarding =
                $request->boolean('needs_forwarding');

            $review = new ReportReview();
            $review->report_id = $lockedReport->id;
            $review->reviewer_id = $request->user()->id;
            $review->notes = $validated['notes'];
            $review->needs_forwarding = $needsForwarding;
            $review->save();

            $lockedReport->status = $needsForwarding
                ? 'needs_forwarding'
                : 'closed';

            $lockedReport->save();
        });

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Hasil peninjauan berhasil disimpan.');
    }
}
