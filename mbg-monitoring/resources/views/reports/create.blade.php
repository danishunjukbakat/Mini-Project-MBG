
@extends('layouts.app')

@section('content')
    <h1>Buat Laporan Kejadian</h1>

    <p>
        Pilih sekolah dan distribusi makanan
        yang berkaitan dengan laporan.
    </p>

    <form
        id="report-form"
        method="POST"
        action="{{ route('reports.store') }}"
        enctype="multipart/form-data"
        data-distributions-url="{{ route('schools.distributions.index', ['school' => 'SCHOOL_ID_PLACEHOLDER']) }}"
        data-old-distribution="{{ old('distribution_id', '') }}"
    >
        @csrf

        <label for="school_id">Sekolah</label>

        <select name="school_id" id="school_id" required>
            <option value="">-- Pilih Sekolah --</option>

            @foreach($schools as $school)
                <option
                    value="{{ $school->id }}"
                    @selected(old('school_id', request('school_id')) == $school->id)
                >
                    {{ $school->name }}
                </option>
            @endforeach
        </select>

        <label for="distribution_id">
            Distribusi Makanan
        </label>

        <select
            name="distribution_id"
            id="distribution_id"
            required
            disabled
        >
            <option value="">
                -- Pilih sekolah terlebih dahulu --
            </option>
        </select>

        <p id="distribution-status" aria-live="polite"></p>

        <label for="title">Judul Laporan</label>

        <input
            type="text"
            name="title"
            id="title"
            maxlength="150"
            value="{{ old('title') }}"
            required
        >

        <label for="incident_date">
            Tanggal Kejadian
        </label>

        <input
            type="date"
            name="incident_date"
            id="incident_date"
            max="{{ now()->toDateString() }}"
            value="{{ old('incident_date') }}"
            required
        >

        <label for="description">
            Deskripsi Kejadian
        </label>

        <textarea
            name="description"
            id="description"
            rows="6"
            required
        >{{ old('description') }}</textarea>

        <label for="evidence">
            Bukti Pendukung (Opsional)
        </label>

        <input
            type="file"
            name="evidence"
            id="evidence"
            accept=".jpg,.jpeg,.png,.pdf"
        >

        <small>
            Format JPG, JPEG, PNG, atau PDF.
            Maksimal 2 MB.
        </small>

        <p>
            <button type="submit">
                Simpan Laporan
            </button>
        </p>
    </form>

    <script
        src="{{ asset('js/report-distributions.js') }}"
        defer
    ></script>
@endsection
