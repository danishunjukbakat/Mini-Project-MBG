<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    // Menampilkan daftar laporan
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Report::class);

        $query = Report::with([
            'reporter',
            'distribution.school'
        ])->latest();

        // Pelapor hanya melihat laporan miliknya
        if ($request->user()->role === 'pelapor') {
            $query->where(
                'reporter_id',
                $request->user()->id
            );
        }

        $reports = $query->get();

        return view('reports.index', compact('reports'));
    }

    // Menampilkan form laporan baru
    public function create(Request $request): View
    {
        Gate::authorize('create', Report::class);

        $schools = School::orderBy('name')->get();

        $selectedSchool = null;
        $distributions = collect();

        if ($request->filled('school_id')) {
            $selectedSchool = School::find(
                $request->query('school_id')
            );

            if ($selectedSchool) {
                $distributions = $selectedSchool
                    ->distributions()
                    ->orderByDesc('distribution_date')
                    ->get();
            }
        }

        return view('reports.create', compact(
            'schools',
            'selectedSchool',
            'distributions'
        ));
    }

    // Menyimpan laporan baru
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Report::class);

        $validated = $request->validate([
            'school_id' => [
                'required',
                'integer',
                Rule::exists('schools', 'id'),
            ],
            'distribution_id' => [
                'required',
                'integer',
                Rule::exists('distributions', 'id')
                    ->where(
                        'school_id',
                        $request->input('school_id')
                    ),
            ],
            'title' => [
                'required', 'string', 'min:5', 'max:150'
            ],
            'incident_date' => [
                'required', 'date', 'before_or_equal:today'
            ],
            'description' => [
                'required', 'string', 'min:10', 'max:2000'
            ],
        ]);

        $report = new Report();

        // Identitas pelapor ditentukan server
        $report->reporter_id = $request->user()->id;

        $report->distribution_id =
            $validated['distribution_id'];

        $report->title = $validated['title'];
        $report->incident_date =
            $validated['incident_date'];

        $report->description =
            $validated['description'];

        $report->status = 'submitted';

        $report->save();

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Laporan berhasil dibuat.');
    }

    // Melihat detail laporan
    public function show(Report $report): View
    {
        Gate::authorize('view', $report);

        $report->load([
            'reporter',
            'distribution.school',
        ]);

        return view('reports.show', compact('report'));
    }

    // Menampilkan form edit
    public function edit(Report $report): View
    {
        Gate::authorize('update', $report);

        $report->load('distribution.school');

        return view('reports.edit', compact('report'));
    }

    // Menyimpan perubahan laporan
    public function update(
        Request $request,
        Report $report
    ): RedirectResponse {
        Gate::authorize('update', $report);

        $validated = $request->validate([
            'title' => [
                'required', 'string', 'min:5', 'max:150'
            ],
            'incident_date' => [
                'required', 'date', 'before_or_equal:today'
            ],
            'description' => [
                'required', 'string', 'min:10', 'max:2000'
            ],
        ]);

        $report->update($validated);

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Laporan berhasil diperbarui.');
    }
}
