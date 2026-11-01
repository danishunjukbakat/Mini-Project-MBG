@extends('layouts.app')

@section('content')
    <h1>Edit Laporan</h1>

    <p>
        Sekolah:
        <strong>{{ $report->distribution->school->name }}</strong>
    </p>

    <p>
        Distribusi:
        <strong>{{ $report->distribution->code }}</strong>
    </p>

    <form method="POST"
          action="{{ route('reports.update', $report) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <label for="title">Judul Laporan</label>
        <input type="text"
               id="title"
               name="title"
               maxlength="150"
               value="{{ old('title', $report->title) }}"
               required>

        <label for="incident_date">Tanggal Kejadian</label>
        <input type="date"
               id="incident_date"
               name="incident_date"
               max="{{ now()->toDateString() }}"
               value="{{ old('incident_date', $report->incident_date->format('Y-m-d')) }}"
               required>

        <label for="description">Deskripsi Kejadian</label>
        <textarea id="description"
                  name="description"
                  rows="6"
                  required>{{ old('description', $report->description) }}</textarea>
        
        <h3>Bukti Pendukung</h3>

        @if($report->evidence_path)
            <p>
                <a href="{{ route('reports.evidence', $report) }}">
                    Download Bukti Saat Ini
                </a>
            </p>
        @endif

        <label for="evidence">Ganti File Bukti (Opsional)</label>

        <input
            type="file"
            id="evidence"
            name="evidence"
            accept=".jpg,.jpeg,.png,.pdf"
        >

        <small>
            Kosongkan jika tidak ingin mengganti file.
            Maksimal 2 MB.
        </small>
        <button type="submit">Simpan Perubahan</button>
    </form>
@endsection
