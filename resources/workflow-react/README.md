# INRM Workflow React Frontend

A small React app (Vite) to manage INRM Workflow automations via the Laravel API.

## Quick start
```bash
npm install
npm run dev
```
- By default it calls relative paths (`/api/...`). If your API is under a different origin, create `.env` with:
```
VITE_API_BASE=http://localhost:8000
```

## Build for production
```bash
npm run build
# deploy ./dist to your web server or wire into Laravel Vite
```

## Embedding in Laravel (optional)
- Copy this folder to `resources/workflow-react`.
- Add a Vite entry in your Laravel `vite.config.js` to include `resources/workflow-react/src/main.tsx`.
- Create a Blade view that mounts `<div id="root"></div>` and includes the Vite entry with `@vite(...)`.
