import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ["resources/js/app.js", "resources/css/app.css"],
            refresh: true,
        }),
    ],
    server: {
        proxy: {
            "/api": "http://127.0.0.1:8000", // Proxy API requests to Laravel
        },
        historyApiFallback: true, // Đảm bảo rằng Vite xử lý các route của SPA
    },
    resolve: {
        alias: {
            "@": "/resources",
        },
    },
    define: {
        "process.env": process.env, // Không cần thiết nếu chỉ dùng import.meta.env
    },
});
