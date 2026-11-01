@extends('layouts.app')

@section('content')
    <h1>Daftar Laporan MBG</h1>

    @can('create', \App\Models\Report::class)
        <a class="button" href="{{ route('reports.create') }}">
            + Buat Laporan
        </a>
    @endcan

    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Sekolah</th>
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
                    <td colspan="4">Belum ada laporan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
