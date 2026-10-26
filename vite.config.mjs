import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => ({
  plugins: [
    laravel({
      input: [
        // Legacy Blade pages: jQuery, Bootstrap 3 and select2, plus their Less
        'resources/assets/js/app.js',
        'resources/assets/less/application.less',
        // Inertia pages
        'resources/js/react-app.tsx',
      ],
      refresh: true,
    }),
    react(),
  ],
  resolve: {
    // Matches the "@/*" path in tsconfig.json
    alias: { '@': fileURLToPath(new URL('./resources/js', import.meta.url)) },
  },
  // `yarn dev` and `yarn watch` build in development mode: readable, with source maps
  build: mode === 'development' ? { minify: false, sourcemap: true } : {},
}));
