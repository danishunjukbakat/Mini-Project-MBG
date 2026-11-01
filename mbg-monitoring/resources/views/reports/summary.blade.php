@extends('layouts.app')

@section('content')
    <h1>Rekap Laporan MBG</h1>

    <p>
        Ringkasan pelaporan dan pemantauan
        keamanan makanan MBG.
    </p>

    <h2>Filter Sekolah</h2>

    <form method="GET"
          action="{{ route('reports.summary') }}">

        <label for="school_id">Pilih Sekolah</label>

        <select name="school_id" id="school_id">
            <option value="">Semua Sekolah</option>

            @foreach($schools as $school)
                <option value="{{ $school->id }}"
                    @selected(request('school_id') == $school->id)>
                    {{ $school->name }}
                </option>
            @endforeach
        </select>

        <button type="submit">Terapkan Filter</button>

        <a href="{{ route('reports.summary') }}">
            Reset Filter
        </a>
    </form>

    <hr>

    <h2>Ringkasan Laporan</h2>

    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
        gap:15px;
        margin:20px 0;
    ">
        <div style="background:#eff6ff;padding:20px;border-radius:8px;">
            <h3>Total Laporan</h3>
            <h1>{{ $total }}</h1>
        </div>

        <div style="background:#fef3c7;padding:20px;border-radius:8px;">
            <h3>Dalam Proses</h3>
            <h1>{{ $inProgress }}</h1>
        </div>

        <div style="background:#dcfce7;padding:20px;border-radius:8px;">
            <h3>Ditutup</h3>
            <h1>{{ $closed }}</h1>
        </div>
    </div>

    <hr>

    <h2>Tabel Rekap Berdasarkan Status</h2>

    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Jumlah Laporan</th>
            </tr>
        </thead>

        <tbody>
            @forelse($recap as $row)
                <tr>
                    <td>
                        {{ match($row->status) {
                            'submitted' => 'Baru',
                            'under_review' => 'Dalam Peninjauan',
                            'needs_forwarding' => 'Perlu Diteruskan',
                            'forwarded' => 'Sudah Diteruskan',
                            'closed' => 'Ditutup',
                            default => $row->status
                        } }}
                    </td>

                    <td>{{ $row->total }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">
                        Tidak ada laporan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top:20px;">
        <a href="{{ route('reports.index') }}">
            Kembali ke Daftar Laporan
        </a>
    </p>
@endsection
