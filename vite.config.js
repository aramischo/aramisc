import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

/**
 * The bundle is loaded as a classic <script> (see backEnd/partials/footer.blade.php),
 * NOT as type="module" : it must run synchronously before public/chat/js/custom.js
 * and the page scripts, which bind jQuery handlers on the DOM rendered by Vue.
 * Each JS entry is therefore wrapped in an IIFE (no import/export may remain).
 */
const classicScript = {
    name: 'aramisc-classic-script',
    enforce: 'post',
    renderChunk(code, chunk) {
        if (!chunk.isEntry || !chunk.fileName.endsWith('.js')) {
            return null;
        }
        if (/^\s*(import|export)\b/m.test(code)) {
            this.error(`${chunk.fileName} still has import/export : it cannot be loaded as a classic script.`);
        }
        return { code: `(function(){\n${code}\n})();`, map: null };
    },
    // CSS imported by a JS entry (Quill, emoji picker, <style> of the .vue files) is injected
    // at runtime by the script itself, as laravel-mix did, instead of a separate unlinked file.
    generateBundle: {
        handler(options, bundle) {
            for (const chunk of Object.values(bundle)) {
                const imported = chunk.type === 'chunk' && chunk.isEntry && chunk.viteMetadata?.importedCss;
                if (!imported || !imported.size) {
                    continue;
                }
                const wrapper = '(function(){';
                if (!chunk.code.startsWith(wrapper)) {
                    this.error(`${chunk.fileName} is not wrapped : cannot inject its CSS.`);
                }
                const css = [...imported].map((file) => bundle[file].source).join('\n');
                imported.forEach((file) => delete bundle[file]);
                imported.clear();
                chunk.code = wrapper
                    + `var s=document.createElement('style');s.textContent=${JSON.stringify(css)};document.head.appendChild(s);`
                    + chunk.code.slice(wrapper.length);
                // NB : the file hash was computed before this CSS was added (renaming is not possible
                // with Rolldown) ; cache busting relies on the ?v= added by AppServiceProvider.
            }
        },
    },
};

export default defineConfig({
    // The site is served from the project root, assets live under /public/...
    base: '/public/build/',
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/sass/backend_design_v2.scss',
                'resources/js/app.js',
            ],
            refresh: false,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        classicScript,
    ],
    resolve: {
        alias: {
            // Components are compiled from the in-DOM Blade markup at runtime.
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
    },
    build: {
        modulePreload: { polyfill: false },
    },
});
