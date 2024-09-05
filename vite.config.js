import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import obfuscatorPlugin from 'vite-plugin-javascript-obfuscator';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        obfuscatorPlugin({
            include: [
                'resources/js/main.js',
            ],
            exclude: [/node_modules/],
            apply: 'build',
            options: {
                compact: true,
                controlFlowFlattening: false,
                deadCodeInjection: false,
                debugProtection: false,
                disableConsoleOutput: false,
                identifierNamesGenerator: 'mangled',
                identifiersPrefix: 'z',
                renameGlobals: true,
                rotateStringArray: true,
                selfDefending: true,
                stringArray: true,
                stringArrayEncoding: ['base64'],
                stringArrayThreshold: 1,
                transformObjectKeys: false,
                unicodeEscapeSequence: false
            },
        }),
    ],
});
