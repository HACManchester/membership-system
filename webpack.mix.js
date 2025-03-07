const mix = require('laravel-mix');

mix.copy('node_modules/bootstrap/dist/fonts', 'public/fonts');
mix.copy('node_modules/material-design-icons/iconfont', 'public/fonts');

mix.js('resources/assets/js/app.js', 'public/js').react();

mix.less('resources/assets/less/application.less', 'public/css');

mix.version();

mix.options({
    // Don't perform any css url rewriting by default
    processCssUrls: false,
})

if (!mix.inProduction()) {
    mix.sourceMaps();
}