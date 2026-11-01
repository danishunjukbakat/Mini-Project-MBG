
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard MBG</title>
</head>
<body>
    <h1>Dashboard Monitoring MBG</h1>

    <p>
        Selamat datang,
        <strong>{{ auth()->user()->name }}</strong>
    </p>

    <p>
        Role:
        <strong>{{ auth()->user()->role }}</strong>
    </p>

    <p>Anda berhasil login ke aplikasi.</p>

    <form method="POST"
          action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>

    <h2>Menu Laporan</h2>

    <a href="{{ route('reports.index') }}">
        Lihat Daftar Laporan
    </a>

    @can('create', \App\Models\Report::class)
        <a href="{{ route('reports.create') }}">
            Buat Laporan Baru
        </a>
    @endcan
</body>
</html>
