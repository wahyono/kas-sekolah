<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Sekolah API Documentation - Swagger UI</title>
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/favicon-32x32.png" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .custom-nav {
            background: #0f172a;
            color: #ffffff;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .custom-nav .logo-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .custom-nav .brand-badge {
            background: #4f46e5;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .custom-nav .title {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }
        .custom-nav .links {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .custom-nav a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.15s ease;
        }
        .custom-nav a:hover {
            color: #38bdf8;
        }
        .swagger-ui .topbar {
            display: none !important;
        }
        .swagger-ui {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .swagger-ui .info {
            margin: 28px 0 20px !important;
        }
        .swagger-ui .scheme-container {
            background: #ffffff !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1) !important;
            padding: 16px 0 !important;
            margin-bottom: 24px !important;
            border-radius: 8px;
        }
        .swagger-ui .btn.authorize {
            background-color: #4f46e5 !important;
            border-color: #4f46e5 !important;
            color: #ffffff !important;
            border-radius: 6px !important;
            font-weight: 600 !important;
        }
        .swagger-ui .btn.authorize svg {
            fill: #ffffff !important;
        }
    </style>
</head>
<body>
    <header class="custom-nav">
        <div class="logo-group">
            <span class="brand-badge">Kas Sekolah</span>
            <span class="title">Enterprise REST API Documentation</span>
        </div>
        <div class="links">
            <a href="/" target="_blank">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Buka Web App
            </a>
            <a href="/openapi.json" target="_blank" download="kas-sekolah-openapi.json">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download OpenAPI Spec
            </a>
        </div>
    </header>

    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                docExpansion: "list",
                filter: true,
                persistAuthorization: true,
                displayRequestDuration: true,
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1
            });
            window.ui = ui;
        };
    </script>
</body>
</html>
