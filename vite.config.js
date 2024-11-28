import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // css
                'resources/css/app.css',
                'resources/css/common.css',

                // JS
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        viteStaticCopy({
            targets: [
                {
                    src: 'resources/images',  // Path in resources folder
                    dest: 'images'           // Path in public folder
                },
                // {
                //     src: 'resources/svg',     // For SVG files
                //     dest: 'svg'               // Path in public/svg
                // },
            ],
        }),
    ],
});
