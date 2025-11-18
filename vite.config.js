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

        // 🔥 Inject jQuery global untuk plugin-plugin jQuery (daterangepicker)
        inject({
            $: "jquery",
            jQuery: "jquery",
            "window.jQuery": "jquery",
            "window.$": "jquery",
            include: ["**/*.js", "**/*.vue"],
            exclude: ["**/*.css"],
        }),
    ],

    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/js"),
            // pastikan jQuery load versi minified
            jquery: "jquery/dist/jquery.min.js",
        }
    },

    build: {
        sourcemap: false
    }
});
