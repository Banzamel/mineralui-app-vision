import {defineConfig, loadEnv} from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig(({mode}) => {
    const env = loadEnv(mode, process.cwd(), '')
    const backend = env.VITE_BACKEND_URL ?? 'http://laravel-vision:8000'

    return {
        plugins: [react()],
        // MineralUI Pro is consumed as a symlinked file: dep — its dist/ imports
        // `react/jsx-runtime` and `react-dom` which Vite's dep scanner tries to resolve
        // from the symlink target. Listing the package here forces Vite to pre-bundle it
        // through the consumer's node_modules so React resolves correctly.
        optimizeDeps: {
            include: [
                '@banzamel/mineralui-pro',
                '@banzamel/mineralui-pro/cards',
                '@banzamel/mineralui-pro/controls',
                '@banzamel/mineralui-pro/data',
                '@banzamel/mineralui-pro/display',
                '@banzamel/mineralui-pro/feedback',
                '@banzamel/mineralui-pro/i18n',
                '@banzamel/mineralui-pro/icons',
                '@banzamel/mineralui-pro/illustrations',
                '@banzamel/mineralui-pro/inputs',
                '@banzamel/mineralui-pro/layout',
                '@banzamel/mineralui-pro/media',
                '@banzamel/mineralui-pro/theme',
                '@banzamel/mineralui-pro/typography',
            ],
        },
        resolve: {
            // Keep the symlink path so when Pro's dist/ files import `react/jsx-runtime` and
            // `react-dom`, Vite walks up via react-vision/node_modules (where they live) instead
            // of resolving to the symlink target's parent (which doesn't have node_modules in the bind mount).
            preserveSymlinks: true,
            // The mineralui repo carries its own node_modules/react (via Vite mounting the whole
            // monorepo). Without dedupe, esbuild's pre-bundle step pulls react from there and the app
            // ends up with two React instances → "Invalid hook call". Forcing dedupe pins everything
            // to react-vision/node_modules/react.
            dedupe: ['react', 'react-dom', 'react/jsx-runtime', 'react/jsx-dev-runtime'],
        },
        server: {
            host: '0.0.0.0',
            port: 5173,
            // Polling zamiast fsnotify — Windows/macOS bind-mounty w Docker Desktop
            // nie propagują inotify events do kontenera Linux, więc HMR bez polling
            // nie zauważa zmian w plikach. interval 300ms to kompromis CPU/latency.
            watch: {
                usePolling: true,
                interval: 300,
            },
            // HMR klient łączy się po localhost:5173 (port mapowany na hoście),
            // nie po nazwie kontenera (której przeglądarka nie widzi).
            hmr: {
                host: 'localhost',
                port: 5173,
                clientPort: 5173,
            },
            proxy: {
                // Backend API — wszystkie endpointy pod /api, w tym installer (/api/install/*).
                '/api': {target: backend, changeOrigin: true},
                // Passport public grant (nie pod /api, żeby short URL). React nie ma route /oauth.
                '/oauth': {target: backend, changeOrigin: true},
                // Laravel Echo / Reverb auth.
                '/broadcasting': {target: backend, changeOrigin: true},
            },
        },
    }
})
