<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportSummaryController extends Controller
{
    public function index(Request $request): View
    {
        // Hanya Pemantau dan Koordinator
        abort_unless(
            in_array($request->user()->role, [
                'pemantau',
                'koordinator',
            ], true),
            403
        );

        // Validasi filter
        $filters = $request->validate([
            'school_id' => [
                'nullable',
                'integer',
                Rule::exists('schools', 'id'),
            ],
        ]);

        $schoolId = $filters['school_id'] ?? null;

        // Query dasar
        $query = Report::query();

        // Filter sekolah
        if ($schoolId) {
            $query->whereHas(
                'distribution',
                function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                }
            );
        }

        // Tiga informasi ringkasan
        $total = (clone $query)->count();

        $inProgress = (clone $query)
            ->whereIn('status', [
                'submitted',
                'under_review',
                'needs_forwarding',
                'forwarded',
            ])
            ->count();

        $closed = (clone $query)
            ->where('status', 'closed')
            ->count();

        // Rekap berdasarkan status
        $recap = (clone $query)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        // Daftar sekolah untuk filter
        $schools = School::orderBy('name')
            ->get(['id', 'name']);

        return view('reports.summary', compact(
            'total',
            'inProgress',
            'closed',
            'recap',
            'schools'
        ));
    }
}
