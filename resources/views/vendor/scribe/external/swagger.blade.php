<!doctype html>
<html lang="en">
<head>
    <title>{!! $metadata['title'] !!}</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css"/>
    <style>
        html { box-sizing: border-box; overflow-y: scroll; }
        *, *::before, *::after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        SwaggerUIBundle({
            url: "/docs.openapi",
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIBundle.SwaggerUIStandalonePreset,
            ],
            layout: "BaseLayout",
            responseInterceptor: function (response) {
                if (response.url && response.url.endsWith('/docs.openapi')) {
                    try {
                        var spec = typeof response.text === 'string' ? response.text : response.body;
                        if (spec) {
                            spec = spec.replace(
                                /url:\s*'[^']*'/,
                                "url: '" + window.location.origin + "'"
                            );
                            response.text = spec;
                            response.body = spec;
                        }
                    } catch (e) {}
                }
                return response;
            },
        });
    </script>
</body>
</html>
