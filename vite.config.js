import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const vitePort = Number(env.VITE_PORT || 5173);
    const viteHost = env.VITE_HOST || 'localhost';
    const appUrl = env.APP_URL || 'http://localhost:8000';
    const frontendUrl = env.FRONTEND_URL || '';

    return {
        server: {
            host: '0.0.0.0',
            port: vitePort,
            strictPort: true,
            origin: `http://${viteHost}:${vitePort}`,
            cors: {
                origin: [
                    appUrl,
                    ...(frontendUrl ? [frontendUrl] : []),
                    /^https?:\/\/localhost(?::\d+)?$/,
                    /^https?:\/\/127\.0\.0\.1(?::\d+)?$/,
                ],
                credentials: true,
            },
            hmr: {
                host: viteHost,
                port: vitePort,
                protocol: 'ws',
            },
        },
        plugins: [
            laravel({
                input: [
                    'resources/css/landing.css',
                    'resources/css/auth.css',
                    'resources/css/dashboard.css',
                    'resources/css/profile.css',
                    'resources/js/app.js',
                    'resources/js/pages/auth.js',
                ],
                refresh: true,
            }),
        ],
    };
});
