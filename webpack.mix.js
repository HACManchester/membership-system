const mix = require('laravel-mix');

mix.copy('node_modules/bootstrap/dist/fonts', 'public/fonts');
mix.copy('node_modules/material-design-icons/iconfont', 'public/fonts');
mix.copy('resources/images', 'public/img');

mix.js('resources/assets/js/app.js', 'public/js').react();
mix.ts('resources/js/react-app.tsx', 'public/js').react();

mix.less('resources/assets/less/application.less', 'public/css');

mix.version();

if (!mix.inProduction()) {
    mix.sourceMaps();
}