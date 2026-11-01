@extends('layouts.app')

@section('content')
    <h1>Daftar Laporan MBG</h1>

    @can('create', \App\Models\Report::class)
        <a class="button" href="{{ route('reports.create') }}">
            + Buat Laporan
        </a>
    @endcan

    <hr>

    <h2>Pencarian dan Filter</h2>

    <form method="GET"
          action="{{ route('reports.index') }}">

        {{-- SEARCH --}}
        <label for="search">Cari Laporan</label>

        <input
            type="text"
            name="search"
            id="search"
            placeholder="Cari judul atau deskripsi..."
            value="{{ request('search') }}"
            maxlength="150"
        >

        {{-- FILTER STATUS --}}
        <label for="status">Status Laporan</label>

        <select name="status" id="status">
            <option value="">Semua Status</option>

            @foreach([
                'submitted' => 'Baru',
                'under_review' => 'Dalam Peninjauan',
                'needs_forwarding' => 'Perlu Diteruskan',
                'forwarded' => 'Sudah Diteruskan',
                'closed' => 'Selesai',
            ] as $value => $label)

                <option value="{{ $value }}"
                    @selected(request('status') === $value)>
                    {{ $label }}
                </option>

            @endforeach
        </select>

        {{-- FILTER SEKOLAH --}}
        <label for="school_id">Sekolah</label>

        <select name="school_id" id="school_id">
            <option value="">Semua Sekolah</option>

            @foreach($schools as $school)
                <option
                    value="{{ $school->id }}"
                    @selected(request('school_id') == $school->id)
                >
                    {{ $school->name }}
                </option>
            @endforeach
        </select>

        <button type="submit">Terapkan Filter</button>

        <a href="{{ route('reports.index') }}">
            Reset Filter
        </a>
    </form>

    <hr>

    <h2>Hasil Pencarian</h2>

    <p>
        Total {{ $reports->total() }} laporan ditemukan.
    </p>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Sekolah</th>
                    <th>Distribusi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->title }}</td>

                        <td>
                            {{ $report->distribution->school->name }}
                        </td>

                        <td>
                            {{ $report->distribution->code }}
                        </td>

                        <td>{{ $report->status }}</td>

                        <td>
                            <a href="{{ route('reports.show', $report) }}">
                                Detail
                            </a>

                            @can('update', $report)
                                |
                                <a href="{{ route('reports.edit', $report) }}">
                                    Edit
                                </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Tidak ada laporan yang sesuai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($reports->hasPages())
        <nav aria-label="Navigasi halaman"
             style="margin-top: 24px;">

            @if($reports->previousPageUrl())
                <a class="button"
                   href="{{ $reports->previousPageUrl() }}">
                    Sebelumnya
                </a>
            @endif

            <span style="margin: 0 12px;">
                Halaman {{ $reports->currentPage() }}
                dari {{ $reports->lastPage() }}
            </span>

            @if($reports->nextPageUrl())
                <a class="button"
                   href="{{ $reports->nextPageUrl() }}">
                    Berikutnya
                </a>
            @endif
        </nav>
    @endif
@endsection
