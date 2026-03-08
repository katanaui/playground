@props(['components' => [], 'current' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Component Studio - KatanaUI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
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
    <style>
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; color: #111827; background: #fff; }

        .studio-layout { display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar */
        .studio-sidebar {
            width: 244px;
            min-width: 244px;
            background: #fafbfa;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .studio-sidebar-header {
            padding: 14px 18px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .studio-logo {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #111827;
        }
        .studio-sidebar-header h1 {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            letter-spacing: -0.01em;
        }
        .studio-sidebar-header h1 span {
            font-weight: 400;
            color: #6b7280;
        }
        .studio-nav {
            flex: 1;
            overflow-y: auto;
            padding: 6px 0;
        }
        .studio-nav::-webkit-scrollbar { width: 4px; }
        .studio-nav::-webkit-scrollbar-track { background: transparent; }
        .studio-nav::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

        .group-header {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            cursor: pointer;
            user-select: none;
            transition: background 0.12s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        .group-header:hover { background: #fafbfa; }
        .group-header .chevron {
            width: 12px;
            height: 12px;
            transition: transform 0.2s ease;
            color: #9ca3af;
        }
        .group-header.open .chevron { transform: rotate(90deg); }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 14px 5px 30px;
            font-size: 13px;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.12s;
            border-left: 3px solid transparent;
        }
        .nav-item:hover {
            background: #f3f4f6;
            color: #111827;
        }
        .nav-item.active {
            background: #ffebeb;
            color: #ea3e75;
            border-left-color: #ea3e75;
            font-weight: 500;
        }
        .nav-icon {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            opacity: 0.4;
        }
        .nav-item.active .nav-icon { opacity: 0.9; }

        /* Main */
        .studio-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #ffffff;
        }
    </style>
</head>
<body>
    <div class="studio-layout">
        {{-- Sidebar --}}
        <aside class="studio-sidebar">
            <nav class="studio-nav" x-data="{ open: { 'Base': true, 'Layouts': false, 'Effect/Animations': false } }">
                @foreach($components as $group => $items)
                    <div>
                        <button
                            class="group-header"
                            :class="{ 'open': open['{{ $group }}'] }"
                            @click="open['{{ $group }}'] = !open['{{ $group }}']"
                        >
                            <svg class="chevron" viewBox="0 0 12 12" fill="none">
                                <path d="M4.5 2.5l3.5 3.5-3.5 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $group }}
                        </button>
                        <div x-show="open['{{ $group }}']" x-cloak>
                            @foreach($items as $item)
                                <a
                                    href="/component/{{ $item['id'] }}"
                                    class="nav-item {{ $current === $item['id'] ? 'active' : '' }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 14 14" fill="none">
                                        <rect x="1" y="1" width="12" height="12" rx="2.5" stroke="currentColor" stroke-width="1.2"/>
                                        <rect x="4" y="4" width="6" height="6" rx="1" fill="currentColor" opacity="0.25"/>
                                    </svg>
                                    {{ $item['name'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>

        {{-- Main Content --}}
        <main class="studio-main">
            {{ $slot }}
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
