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
            padding: 0px;
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
        }
        /* Tailwind Typography – prose classes (browser CDN doesn't support @plugin) */
        .prose { color: #374151; max-width: 65ch; font-size: 1rem; line-height: 1.75; }
        .prose > :first-child { margin-top: 0; }
        .prose > :last-child { margin-bottom: 0; }
        .prose p { margin-top: 1.25em; margin-bottom: 1.25em; }
        .prose a { color: #111827; text-decoration: underline; font-weight: 500; }
        .prose strong { color: #111827; font-weight: 600; }
        .prose em { font-style: italic; }
        .prose h1 { color: #111827; font-weight: 800; font-size: 2.25em; margin-top: 0; margin-bottom: 0.89em; line-height: 1.11; }
        .prose h2 { color: #111827; font-weight: 700; font-size: 1.5em; margin-top: 2em; margin-bottom: 1em; line-height: 1.33; }
        .prose h3 { color: #111827; font-weight: 600; font-size: 1.25em; margin-top: 1.6em; margin-bottom: 0.6em; line-height: 1.6; }
        .prose h4 { color: #111827; font-weight: 600; margin-top: 1.5em; margin-bottom: 0.5em; line-height: 1.5; }
        .prose h5 { color: #111827; font-weight: 600; margin-top: 1.5em; margin-bottom: 0.5em; line-height: 1.5; }
        .prose h6 { color: #111827; font-weight: 600; margin-top: 1.5em; margin-bottom: 0.5em; line-height: 1.5; }
        .prose blockquote { font-weight: 500; font-style: italic; color: #111827; border-left: 0.25rem solid #e5e7eb; padding-left: 1em; margin-top: 1.6em; margin-bottom: 1.6em; }
        .prose blockquote p:first-of-type::before { content: open-quote; }
        .prose blockquote p:last-of-type::after { content: close-quote; }
        .prose code { color: #111827; font-weight: 600; font-size: 0.875em; }
        .prose code::before { content: '`'; }
        .prose code::after { content: '`'; }
        .prose pre { color: #e5e7eb; background-color: #1f2937; overflow-x: auto; font-weight: 400; font-size: 0.875em; line-height: 1.71; margin-top: 1.71em; margin-bottom: 1.71em; border-radius: 0.375rem; padding: 0.86em 1.14em; }
        .prose pre code { background-color: transparent; border-width: 0; border-radius: 0; padding: 0; font-weight: inherit; color: inherit; font-size: inherit; font-family: inherit; line-height: inherit; }
        .prose pre code::before { content: none; }
        .prose pre code::after { content: none; }
        .prose ul { list-style-type: disc; margin-top: 1.25em; margin-bottom: 1.25em; padding-left: 1.625em; }
        .prose ol { list-style-type: decimal; margin-top: 1.25em; margin-bottom: 1.25em; padding-left: 1.625em; }
        .prose li { margin-top: 0.5em; margin-bottom: 0.5em; }
        .prose ul > li::marker { color: #9ca3af; }
        .prose ol > li::marker { color: #6b7280; font-weight: 400; }
        .prose hr { border-color: #e5e7eb; margin-top: 3em; margin-bottom: 3em; }
        .prose table { width: 100%; table-layout: auto; text-align: left; margin-top: 2em; margin-bottom: 2em; font-size: 0.875em; line-height: 1.71; }
        .prose thead { border-bottom: 1px solid #d1d5db; }
        .prose thead th { color: #111827; font-weight: 600; vertical-align: bottom; padding-right: 0.57em; padding-bottom: 0.57em; padding-left: 0.57em; }
        .prose tbody tr { border-bottom: 1px solid #e5e7eb; }
        .prose tbody td { vertical-align: baseline; padding: 0.57em; }
        .prose img { margin-top: 2em; margin-bottom: 2em; }
        .prose figure { margin-top: 2em; margin-bottom: 2em; }
        .prose figcaption { color: #6b7280; font-size: 0.875em; line-height: 1.43; margin-top: 0.86em; }
        .prose-sm { font-size: 0.875rem; line-height: 1.71; }
        .prose-lg { font-size: 1.125rem; line-height: 1.78; }
        .prose-xl { font-size: 1.25rem; line-height: 1.8; }
        .prose-2xl { font-size: 1.5rem; line-height: 1.67; }
        .dark .prose-invert, .prose-invert { color: #d1d5db; }
        .dark .prose-invert h1, .dark .prose-invert h2, .dark .prose-invert h3, .dark .prose-invert h4, .dark .prose-invert h5, .dark .prose-invert h6,
        .dark .prose-invert strong, .dark .prose-invert a,
        .prose-invert h1, .prose-invert h2, .prose-invert h3, .prose-invert h4, .prose-invert h5, .prose-invert h6,
        .prose-invert strong, .prose-invert a { color: #fff; }
        .prose-invert blockquote { border-left-color: #4b5563; color: #e5e7eb; }
        .prose-invert code { color: #fff; }
        .prose-invert pre { background-color: rgba(0,0,0,0.5); }
        .prose-invert hr { border-color: #374151; }
        .prose-invert thead { border-bottom-color: #4b5563; }
        .prose-invert tbody tr { border-bottom-color: #374151; }
        .prose-invert ul > li::marker { color: #6b7280; }
        .prose-invert ol > li::marker { color: #9ca3af; }
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
