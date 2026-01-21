<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Figtree', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #333;
            }

            .container {
                background: white;
                border-radius: 20px;
                padding: 3rem;
                max-width: 800px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                text-align: center;
            }

            .logo {
                font-size: 4rem;
                font-weight: 600;
                margin-bottom: 1rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 1rem;
                color: #333;
            }

            .info {
                margin-top: 2rem;
                padding: 1.5rem;
                background: #f8f9fa;
                border-radius: 10px;
                text-align: left;
            }

            .info h2 {
                margin-bottom: 1rem;
                color: #667eea;
            }

            .info ul {
                list-style: none;
                padding-left: 0;
            }

            .info li {
                padding: 0.5rem 0;
                border-bottom: 1px solid #dee2e6;
            }

            .info li:last-child {
                border-bottom: none;
            }

            .info code {
                background: #fff;
                padding: 0.2rem 0.5rem;
                border-radius: 4px;
                font-family: monospace;
                color: #e83e8c;
            }

            .status {
                display: inline-block;
                padding: 0.5rem 1rem;
                background: #28a745;
                color: white;
                border-radius: 20px;
                margin-top: 1rem;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">Laravel</div>
            <h1>Docker Base Project Ready!</h1>
            <div class="status">✓ Installation Complete</div>
            
            <div class="info">
                <h2>Project Information</h2>
                <ul>
                    <li><strong>Laravel Version:</strong> {{ app()->version() }}</li>
                    <li><strong>PHP Version:</strong> {{ PHP_VERSION }}</li>
                    <li><strong>Environment:</strong> {{ app()->environment() }}</li>
                    <li><strong>Database:</strong> Connected to MySQL via Docker</li>
                </ul>
            </div>

            <div class="info" style="margin-top: 1rem;">
                <h2>Quick Start Commands</h2>
                <ul>
                    <li><code>make setup</code> - Initial setup (install dependencies, generate key, migrate)</li>
                    <li><code>make up</code> - Start Docker containers</li>
                    <li><code>make down</code> - Stop Docker containers</li>
                    <li><code>make artisan CMD="migrate"</code> - Run migrations</li>
                    <li><code>make shell</code> - Open shell in app container</li>
                    <li><code>make db-shell</code> - Open MySQL shell</li>
                </ul>
            </div>

            <div class="info" style="margin-top: 1rem;">
                <h2>Access Points</h2>
                <ul>
                    <li><strong>Application:</strong> <a href="http://localhost:8000">http://localhost:8000</a></li>
                    <li><strong>phpMyAdmin:</strong> <a href="http://localhost:8080">http://localhost:8080</a></li>
                    <li><strong>MySQL:</strong> localhost:3306</li>
                </ul>
            </div>
        </div>
    </body>
</html>
