<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(120deg, #eff6ff 0%, #ecfdf5 100%);
            color: #0f172a;
        }
        .box {
            width: min(640px, calc(100vw - 32px));
            border-radius: 14px;
            background: #fff;
            border: 1px solid #dbe2ea;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            padding: 28px;
        }
        .links { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 18px; }
        .btn {
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid #dbe2ea;
            color: #0f172a;
            background: #fff;
        }
        .btn.primary { background: #0f766e; color: #fff; border-color: #0f766e; }
    </style>
</head>
<body>
<main class="box">
    <h1 style="margin-top:0;">{{ config('app.name', 'Laravel') }}</h1>
    <p>Dự án đã hỗ trợ song song API và Web module chuẩn Laravel.</p>
    <div class="links">
        @auth
            <a class="btn primary" href="{{ route('dashboard') }}">Dashboard</a>
        @else
            <a class="btn primary" href="{{ route('login') }}">Login</a>
            <a class="btn" href="{{ route('register') }}">Register</a>
        @endauth
        <a class="btn" href="{{ url('/api/v1/docs') }}">API Docs</a>
    </div>
</main>
</body>
</html>
