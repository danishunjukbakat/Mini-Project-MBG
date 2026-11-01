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

    <h3>Bukti Pendukung</h3>

    @if($report->evidence_path)
        <a href="{{ route('reports.evidence', $report) }}">
            Download Bukti Laporan
        </a>
    @else
        <p>Belum ada bukti pendukung.</p>
    @endif


{{-- FITUR PEMANTAU KOMUNITAS --}}

@can('startReview', $report)
    <hr>
    <h3>Peninjauan Laporan</h3>

    <form method="POST"
          action="{{ route('reports.review.start', $report) }}">
        @csrf

        <button type="submit">
            Mulai Peninjauan
        </button>
    </form>
@endcan

@can('review', $report)
    <hr>
    <h3>Hasil Peninjauan</h3>

    <form method="POST"
          action="{{ route('reports.reviews.store', $report) }}">
        @csrf

        <label for="notes">Catatan Peninjauan</label>
        <textarea
            name="notes"
            id="notes"
            rows="5"
            required>{{ old('notes') }}</textarea>

        <label for="needs_forwarding">
            Keputusan Peninjauan
        </label>

        <select name="needs_forwarding"
                id="needs_forwarding" required>
            <option value="">
                -- Pilih Keputusan --
            </option>
            <option value="1" @selected(old('needs_forwarding') === '1')>
                Perlu diteruskan
            </option>
            <option value="0" @selected(old('needs_forwarding') === '0')>
                Tidak perlu diteruskan
            </option>
        </select>

        <button type="submit">
            Simpan Hasil Peninjauan
        </button>
    </form>
@endcan

{{-- FITUR KOORDINATOR KOMUNITAS --}}

@can('forward', $report)
    <hr>
    <h3>Catat Penerusan Informasi</h3>

    <form method="POST"
          action="{{ route('reports.forwardings.store', $report) }}">
        @csrf

        <label for="recipient_category">
            Kategori Penerima
        </label>

        <select name="recipient_category"
                id="recipient_category" required>
            <option value="">-- Pilih Penerima --</option>
            <option value="school">Sekolah</option>
            <option value="sppg">SPPG</option>
            <option value="health_facility">
                Fasilitas Kesehatan
            </option>
            <option value="other">Pihak Lain</option>
        </select>

        <label for="recipient_name">Nama Penerima</label>
        <input type="text"
               id="recipient_name"
               name="recipient_name"
               maxlength="150"
               value="{{ old('recipient_name') }}"
               required>

        <label for="forwarding_notes">
            Catatan Penerusan
        </label>

        <textarea name="notes"
                  id="forwarding_notes"
                  rows="4">{{ old('notes') }}</textarea>

        <button type="submit">
            Catat Penerusan
        </button>
    </form>
@endcan

@can('close', $report)
    <hr>
    <h3>Selesaikan Proses</h3>

    <form method="POST"
          action="{{ route('reports.close', $report) }}">
        @csrf

        <button type="submit">
            Tutup Laporan
        </button>
    </form>
@endcan


@if(in_array(auth()->user()->role, ['pemantau', 'koordinator'], true))
    <hr>
    <h3>Riwayat Peninjauan</h3>

    @forelse($report->reviews as $review)
        <p>
            <strong>{{ $review->reviewer->name }}</strong>
            — {{ $review->created_at->format('d-m-Y H:i') }}
        </p>

        <p>{{ $review->notes }}</p>

        <p>
            Keputusan:
            {{ $review->needs_forwarding
                ? 'Perlu diteruskan'
                : 'Tidak perlu diteruskan' }}
        </p>
    @empty
        <p>Belum ada catatan peninjauan.</p>
    @endforelse

    <hr>
    <h3>Riwayat Penerusan</h3>

    @forelse($report->forwardings as $forwarding)
        <p>
            <strong>{{ $forwarding->recipient_name }}</strong>
            ({{ $forwarding->recipient_category }})
        </p>

        <p>
            Dicatat oleh:
            {{ $forwarding->coordinator->name }}
        </p>

        <p>
            Tanggal:
            {{ $forwarding->forwarded_at->format('d-m-Y H:i') }}
        </p>

        <p>{{ $forwarding->notes }}</p>
    @empty
        <p>Belum ada catatan penerusan.</p>
    @endforelse
@endif

@endsection
