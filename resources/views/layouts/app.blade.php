<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        :root {
            --bg: #f8fafc;
            --text: #0f172a;
            --muted: #475569;
            --primary: #0f766e;
            --line: #e2e8f0;
            --white: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #eff6ff 0, #f8fafc 120px);
            color: var(--text);
        }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 24px;
            border-bottom: 1px solid var(--line);
            background: var(--white);
        }
        .brand { font-weight: 700; text-decoration: none; color: var(--text); }
        .nav-links { display: flex; align-items: center; gap: 14px; }
        .nav-links a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .logout-btn {
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 8px;
            padding: 8px 12px;
            cursor: pointer;
            font-weight: 600;
        }
        .container {
            max-width: 1100px;
            margin: 28px auto;
            padding: 0 20px;
        }
        .panel {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--white);
            padding: 24px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }
        .muted { color: var(--muted); }
        .status {
            margin-bottom: 14px;
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
<nav class="nav">
    <a class="brand" href="{{ route('dashboard') }}">{{ config('app.name', 'Laravel') }}</a>
    <div class="nav-links">
        <span class="muted">{{ auth()->user()->name ?? '' }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</nav>
<main class="container">
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
