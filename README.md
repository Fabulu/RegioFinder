# Regiomap – Full Project (Frontend + Backend)

This repo contains a Vue frontend and a backend with a PHP API (plus an optional Python infer service).

Repo layout (important bits)
- /frontend                Vue 3 + Vite app
- /backend/src/Api         PHP API code (ApiResponse.php)
- /backend/src/Config      DB config loader (ConfigLoad.php)
- /backend/web             API entry + Swagger/OpenAPI files
- /backend/setup           database.sql + setup.php helper
- /backend/php             index.php (legacy/alt entry)
- /backend/infer           optional Python service (app.py + Dockerfile)

Run frontend (Windows / PowerShell)
Set-Location frontend
npm install
npm run dev
Open http://localhost:5173

Run backend (local PHP)
You need PHP 8+ and MySQL.
Create DB using backend/setup/database.sql (import into MySQL).
Adjust DB credentials in backend/src/Config/ConfigLoad.php.
Serve the backend web root:
Set-Location backend/web
php -S localhost:8080
Open http://localhost:8080

API basics
GET http://localhost:8080/api.php           returns all POIs
GET http://localhost:8080/api.php?id=1      returns one POI
POST http://localhost:8080/api.php          JSON body: {"poi":"Name"} inserts into poi table

Docker Compose
Create docker-compose.yml at repo root (or in /backend, your choice) to run MySQL + PHP (and optionally infer).

Notes
Swagger/OpenAPI lives in /backend/web (openapi.json + swagger/).
Composer autoload is in /backend/vendor (composer.json at backend root).
