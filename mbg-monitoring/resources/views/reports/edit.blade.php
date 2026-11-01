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
          action="{{ route('reports.update', $report) }}">
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

        <button type="submit">Simpan Perubahan</button>
    </form>
@endsection
