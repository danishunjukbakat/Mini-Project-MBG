<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    // Menampilkan daftar laporan
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Report::class);

        // 1. Validasi parameter pencarian dan filter
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => [
                'nullable',
                Rule::in([
                    'submitted',
                    'under_review',
                    'needs_forwarding',
                    'forwarded',
                    'closed',
                ]),
            ],
            'school_id' => [
                'nullable',
                'integer',
                Rule::exists('schools', 'id'),
            ],
        ]);

        // 2. Query dasar dan relationship
        $query = Report::query()
            ->with([
                'reporter',
                'distribution.school',
            ]);

        // 3. Ownership: Pelapor hanya melihat miliknya
        if ($request->user()->role === 'pelapor') {
            $query->where(
                'reporter_id',
                $request->user()->id
            );
        }

        // 4. Pencarian judul atau deskripsi
        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 5. Filter status laporan
        if (!empty($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        // 6. Filter berdasarkan sekolah
        if (!empty($filters['school_id'])) {
            $query->whereHas(
                'distribution',
                function ($q) use ($filters) {
                    $q->where(
                        'school_id',
                        $filters['school_id']
                    );
                }
            );
        }

        // 7. Urutkan dan batasi 5 laporan per halaman
        $reports = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        // 8. Data sekolah untuk dropdown filter
        $schools = School::orderBy('name')
            ->get(['id', 'name']);

        return view('reports.index', compact(
            'reports',
            'schools'
        ));
    }


    // Menampilkan form laporan baru
    public function create(): View
    {
        Gate::authorize('create', Report::class);

        $schools = School::orderBy('name')
            ->get(['id', 'name']);

        return view('reports.create', compact('schools'));
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

            'evidence' => [
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048',
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

        $newPath = null;

        if ($request->hasFile('evidence')) {
            $newPath = $request->file('evidence')
                ->store('report-evidence', 'local');

            if ($newPath === false) {
                return back()
                    ->withErrors([
                        'evidence' => 'Gagal menyimpan file bukti.',
                    ])
                    ->withInput();
            }

            $report->evidence_path = $newPath;
        }

        try {
            $report->save();
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }

            throw $e;
        }

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

        // Catatan internal hanya dimuat untuk petugas komunitas.
        if (in_array(auth()->user()->role, [
            'pemantau',
            'koordinator',
        ], true)) {
            $report->load([
                'reviews.reviewer',
                'forwardings.coordinator',
            ]);
        }

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
            'evidence' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048',
            ],
        ]);

        $report->fill([
            'title' => $validated['title'],
            'incident_date' => $validated['incident_date'],
            'description' => $validated['description'],
        ]);

        $oldPath = $report->evidence_path;
        $newPath = null;

        if ($request->hasFile('evidence')) {
            $newPath = $request->file('evidence')
                ->store('report-evidence', 'local');

            if ($newPath === false) {
                return back()->withErrors([
                    'evidence' => 'Gagal menyimpan file bukti.',
                ]);
            }

            $report->evidence_path = $newPath;
        }

        try {
            $report->save();
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }

            throw $e;
        }

        // Hapus file lama setelah perubahan berhasil.
        if ($newPath && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    public function downloadEvidence(
        Report $report
    ): StreamedResponse {
        Gate::authorize('view', $report);

        if (
                !$report->evidence_path ||
                !Storage::disk('local')->exists($report->evidence_path)
        ) {
                abort(404, 'File bukti tidak ditemukan.');
        }

        return Storage::disk('local')
            ->download($report->evidence_path);
    }

}
