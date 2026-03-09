<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --font-sans: "Instrument Sans", ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }
        @theme {
            --color-background: var(--background);
            --color-foreground: var(--foreground);
            --color-card: var(--card);
            --color-card-foreground: var(--card-foreground);
            --color-popover: var(--popover);
            --color-popover-foreground: var(--popover-foreground);
            --color-primary: var(--primary);
            --color-primary-foreground: var(--primary-foreground);
            --color-secondary: var(--secondary);
            --color-secondary-foreground: var(--secondary-foreground);
            --color-muted: var(--muted);
            --color-muted-foreground: var(--muted-foreground);
            --color-accent: var(--accent);
            --color-accent-foreground: var(--accent-foreground);
            --color-destructive: var(--destructive);
            --color-border: var(--border);
            --color-input: var(--input);
            --color-ring: var(--ring);
            --radius: var(--radius);
            --radius-medium: var(--radius-medium);
            --radius-large: var(--radius-large);
        }
        :root {
            --background: #ffffff;
            --foreground: #0a0a0a;
            --card: #ffffff;
            --card-foreground: #0a0a0a;
            --popover: #ffffff;
            --popover-foreground: #0a0a0a;
            --primary: #1c1917;
            --primary-foreground: #ffffff;
            --secondary: #f5f5f5;
            --secondary-foreground: #262626;
            --muted: #f5f5f5;
            --muted-foreground: #737373;
            --accent: #f5f5f5;
            --accent-foreground: #171717;
            --destructive: #e7000b;
            --border: #e5e5e5;
            --input: #e5e5e5;
            --ring: #a1a1a1;
            --radius: 0.5rem;
            --radius-medium: 1rem;
            --radius-large: 1.5rem;
        }
        .dark {
            --background: #0a0a0a;
            --foreground: #ffffff;
            --card: #171717;
            --card-foreground: #fafafa;
            --popover: #171717;
            --popover-foreground: #fafafa;
            --primary: #ffffff;
            --primary-foreground: #1c1917;
            --secondary: #262626;
            --secondary-foreground: #f5f5f5;
            --muted: #262626;
            --muted-foreground: #a1a1a1;
            --accent: #262626;
            --accent-foreground: #fafafa;
            --destructive: #b60003;
            --border: rgba(255, 255, 255, 0.1);
            --input: rgba(255, 255, 255, 0.15);
            --ring: #737373;
            --radius: 0.5rem;
            --radius-medium: 1rem;
            --radius-large: 1.5rem;
        }
    </style>
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @foreach($studioScripts ?? [] as $scriptUrl)
        <script src="{{ $scriptUrl }}"></script>
    @endforeach
    @if(!empty($studioHead))
        {!! $studioHead !!}
    @endif
    <style>
        [x-cloak] { display: none !important; }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body>
    {!! $rendered !!}
    @livewireScripts
    <script>
    // Monkey-patch fetch() to intercept local requests and route through WASM via postMessage
    (function() {
        var _originalFetch = window.fetch;
        window.fetch = function(input, init) {
            var url = typeof input === 'string' ? input : (input && input.url ? input.url : '');
            var isLocal = url.startsWith('/') || url.match(/^https?:\/\/localhost/);
            if (!isLocal) return _originalFetch.apply(this, arguments);

            var path = url.replace(/^https?:\/\/localhost/, '');
            var method = (init && init.method) || 'GET';
            var body = (init && init.body) || null;
            var headers = {};
            if (init && init.headers) {
                if (typeof init.headers.forEach === 'function') {
                    init.headers.forEach(function(v, k) { headers[k] = v; });
                } else if (typeof init.headers === 'object') {
                    var keys = Object.keys(init.headers);
                    for (var i = 0; i < keys.length; i++) { headers[keys[i]] = init.headers[keys[i]]; }
                }
            }

            var requestId = 'wf-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

            return new Promise(function(resolve, reject) {
                var timeout = setTimeout(function() {
                    window.removeEventListener('message', handler);
                    reject(new Error('WASM fetch timeout'));
                }, 30000);

                function handler(e) {
                    if (e.data && e.data.type === 'wasm-fetch-response' && e.data.requestId === requestId) {
                        window.removeEventListener('message', handler);
                        clearTimeout(timeout);
                        resolve(new Response(e.data.body, {
                            status: e.data.status || 200,
                            headers: e.data.headers || {}
                        }));
                    }
                }
                window.addEventListener('message', handler);
                window.parent.postMessage({
                    type: 'wasm-fetch',
                    requestId: requestId,
                    url: path,
                    method: method,
                    body: typeof body === 'string' ? body : null,
                    headers: headers
                }, '*');
            });
        };
    })();

    // Relay wasm-fetch messages from child iframes to parent, and responses back down
    window.addEventListener('message', function(e) {
        if (e.data && e.data.type === 'wasm-fetch' && e.source !== window.parent) {
            window.parent.postMessage(e.data, '*');
            if (!window._wasmFetchSources) window._wasmFetchSources = {};
            window._wasmFetchSources[e.data.requestId] = e.source;
        }
        if (e.data && e.data.type === 'wasm-fetch-response') {
            var source = window._wasmFetchSources && window._wasmFetchSources[e.data.requestId];
            if (source) {
                try { source.postMessage(e.data, '*'); } catch(ex) {}
                delete window._wasmFetchSources[e.data.requestId];
            }
        }
    });
    </script>
</body>
</html>
