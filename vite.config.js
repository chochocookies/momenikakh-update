import fs from 'node:fs';
import path from 'node:path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// TPL-05: every template folder contributes its own theme.css (and theme.js when it has one) automatically,
// so adding a template never means editing this file. An invitation page loads invitation-core + ONE theme.css;
// theme.js is imported dynamically by invitation/core.js and becomes its own chunk.
const templatesDir = path.resolve('resources/templates');
const templateEntries = (file) => (fs.existsSync(templatesDir)
    ? fs.readdirSync(templatesDir, { withFileTypes: true })
        .filter((d) => d.isDirectory() && !d.name.startsWith('_') && !d.name.startsWith('.'))
        .filter((d) => fs.existsSync(path.join(templatesDir, d.name, file)))
        .map((d) => `resources/templates/${d.name}/${file}`)
    : []);

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/public.js',
                'resources/css/landing.css',
                'resources/js/landing.js',
                'resources/css/invitation-core.css',
                'resources/js/invitation/core.js',
                ...templateEntries('theme.css'),
                ...templateEntries('theme.js'),
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
