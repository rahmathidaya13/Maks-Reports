import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import inject from "@rollup/plugin-inject";
import path from "path";
export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false
                }
            }
        }),
        inject({
            "window.jQuery": "jquery",
            "window.$": "jquery",
            $: "jquery",
            jQuery: "jquery",

            // ✅ Batasi hanya file .js
            include: ['**/*.js', '**/*.vue'],
            exclude: ['**/*.css']
        })
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, "resources/js"),
            jquery: "jquery/dist/jquery.min.js", // pastikan alias ini ada
        }
    },
    build: {
        sourcemap: false // Jangan menghasilkan sourcemap
    }
});
