import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import inject from "@rollup/plugin-inject";

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
      jQuery: "jquery"
    })
  ],
  resolve: {
    alias: {
      jquery: "jquery/dist/jquery.min.js" // pastikan alias ini ada
    }
  },
  build: {
    sourcemap: false // Jangan menghasilkan sourcemap
  }
});
