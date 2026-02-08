<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #0f766e;
            --border: #dbe2ea;
            --danger: #b91c1c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top right, #dff7f3, var(--bg) 45%);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 440px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }
        h1 { margin-top: 0; margin-bottom: 4px; font-size: 1.5rem; }
        p { margin: 0 0 16px; color: var(--muted); }
        .field { margin-bottom: 14px; }
        .field label { display: block; margin-bottom: 6px; font-weight: 600; }
        .field input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
        }
        .row { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin: 14px 0; }
        .actions { margin-top: 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .btn {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            cursor: pointer;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .link { color: var(--primary); text-decoration: none; font-weight: 600; }
        .error-list {
            margin: 0 0 14px;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: var(--danger);
            font-size: 0.9rem;
        }
        .status {
            margin: 0 0 14px;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<main class="card">
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <ul class="error-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @yield('content')
</main>
</body>
</html>
