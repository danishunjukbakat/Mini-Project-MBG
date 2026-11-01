<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Monitoring MBG</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            color: #1f2937;
        }
        nav {
            background: #1e40af;
            color: white;
            padding: 16px 24px;
        }
        nav a {
            color: white;
            margin-right: 20px;
        }
        main {
            max-width: 960px;
            margin: 30px auto;
            padding: 24px;
            background: white;
            border-radius: 10px;
        }
        input, select, textarea {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 8px 0 18px;
            box-sizing: border-box;
        }
        button, .button {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 16px;
            border: 0;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border-bottom: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .error { color: #dc2626; }
        .success { color: #15803d; }
    </style>
</head>
<body>
    <nav>
        <strong>Monitoring MBG</strong>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('reports.index') }}">Laporan</a>
    </nav>

    <main>
        @if(session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif

        @if($errors->any())
            <ul class="error">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        @yield('content')
    </main>
</body>
</html>
