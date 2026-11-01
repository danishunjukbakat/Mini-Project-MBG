@extends('layouts.app')

@section('content')
    <h1>Buat Laporan Kejadian</h1>

    <p>Langkah 1: Pilih sekolah terlebih dahulu.</p>

    <form method="GET" action="{{ route('reports.create') }}">
        <label for="school_id">Sekolah</label>

        <select name="school_id" id="school_id" required>
            <option value="">-- Pilih Sekolah --</option>

            @foreach($schools as $school)
                <option value="{{ $school->id }}"
                    @selected($selectedSchool?->id == $school->id)>
                    {{ $school->name }}
                </option>
            @endforeach
        </select>

        <button type="submit">Pilih Sekolah</button>
    </form>

    @if($selectedSchool)
        <hr>

        <h2>Informasi Laporan</h2>
        <p>
            Sekolah: <strong>{{ $selectedSchool->name }}</strong>
        </p>

        @if($distributions->isEmpty())
            <p>Belum ada distribusi untuk sekolah ini.</p>
        @else
            <form method="POST"
                  action="{{ route('reports.store') }}">
                @csrf

                <input type="hidden"
                       name="school_id"
                       value="{{ $selectedSchool->id }}">

                <label for="distribution_id">
                    Distribusi Makanan
                </label>

                <select name="distribution_id"
                        id="distribution_id" required>
                    <option value="">-- Pilih Distribusi --</option>

                    @foreach($distributions as $distribution)
                        <option value="{{ $distribution->id }}"
                            @selected(old('distribution_id') == $distribution->id)>
                            {{ $distribution->code }}
                            - {{ $distribution->distribution_date }}
                        </option>
                    @endforeach
                </select>

                <label for="title">Judul Laporan</label>
                <input type="text"
                       id="title"
                       name="title"
                       maxlength="150"
                       value="{{ old('title') }}"
                       required>

                <label for="incident_date">Tanggal Kejadian</label>
                <input type="date"
                       id="incident_date"
                       name="incident_date"
                       max="{{ now()->toDateString() }}"
                       value="{{ old('incident_date') }}"
                       required>

                <label for="description">Deskripsi Kejadian</label>
                <textarea id="description"
                          name="description"
                          rows="6"
                          required>{{ old('description') }}</textarea>

                <button type="submit">Simpan Laporan</button>
            </form>
        @endif
    @endif
@endsection
