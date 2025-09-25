import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.jsx", "resources/workflow-react/src/main.tsx"],
            refresh: true,
        }),
        react(), // ✅ Needed for React + fast refresh
        tailwindcss(),
    ],
    server: {
        proxy: {
            "/api": {
                target: "http://127.0.0.1:8000",
                changeOrigin: true,
                secure: false,
            },
        },
    },
});
