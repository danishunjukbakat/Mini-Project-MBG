<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Monitoring MBG</title>
</head>
<body>
    <h1>{{ $title }}</h1>

    <p>
        Selamat datang,
        {{ auth()->user()->name }}
    </p>

    <p>
        Role Anda:
        <strong>{{ auth()->user()->role }}</strong>
    </p>

    <p>
        Anda berhasil mengakses halaman sesuai
        kewenangan.
    </p>

    <a href="{{ route('dashboard') }}">
        Kembali ke Dashboard
    </a>
</body>
</html>
