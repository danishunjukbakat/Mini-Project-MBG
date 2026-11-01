<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportForwarding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ReportForwardingController extends Controller
{
    // Mencatat penerusan informasi.
    public function store(
        Request $request,
        Report $report
    ): RedirectResponse {
        Gate::authorize('forward', $report);

        $validated = $request->validate([
            'recipient_category' => [
                'required',
                Rule::in([
                    'school',
                    'sppg',
                    'health_facility',
                    'other',
                ]),
            ],
            'recipient_name' => [
                'required', 'string', 'max:150'
            ],
            'notes' => [
                'nullable', 'string', 'max:2000'
            ],
        ]);

        DB::transaction(function () use ($request, $report, $validated) {
            $lockedReport = Report::query()
                ->whereKey($report->id)
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('forward', $lockedReport);

            $forwarding = new ReportForwarding();

            $forwarding->report_id = $lockedReport->id;
            $forwarding->coordinator_id =
                $request->user()->id;

            $forwarding->recipient_category =
                $validated['recipient_category'];

            $forwarding->recipient_name =
                $validated['recipient_name'];

            $forwarding->notes =
                $validated['notes'] ?? null;

            $forwarding->forwarded_at = now();
            $forwarding->save();

            $lockedReport->status = 'forwarded';
            $lockedReport->save();
        });

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Penerusan informasi berhasil dicatat.');
    }

    // Menutup proses administratif laporan.
    public function close(Report $report): RedirectResponse
    {
        DB::transaction(function () use ($report) {
            $lockedReport = Report::query()
                ->whereKey($report->id)
                ->lockForUpdate()
                ->firstOrFail();

            Gate::authorize('close', $lockedReport);

            $lockedReport->status = 'closed';
            $lockedReport->save();
        });

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Proses laporan berhasil ditutup.');
    }
}
