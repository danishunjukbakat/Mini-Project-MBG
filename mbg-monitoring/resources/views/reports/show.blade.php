@extends('layouts.app')

@section('content')
    <h1>Detail Laporan</h1>

    <p><strong>Judul:</strong> {{ $report->title }}</p>
    <p><strong>Sekolah:</strong>
        {{ $report->distribution->school->name }}
    </p>
    <p><strong>Distribusi:</strong>
        {{ $report->distribution->code }}
    </p>
    <p><strong>Tanggal Kejadian:</strong>
        {{ $report->incident_date->format('d-m-Y') }}
    </p>
    <p><strong>Status:</strong>
        {{ $report->status }}
    </p>

    <h3>Deskripsi Kejadian</h3>
    <p style="white-space: pre-wrap">{{ $report->description }}</p>

    @can('update', $report)
        <a class="button"
           href="{{ route('reports.edit', $report) }}">
            Edit Laporan
        </a>
    @endcan

    <a href="{{ route('reports.index') }}">
        Kembali ke Daftar
    </a>
@endsection
