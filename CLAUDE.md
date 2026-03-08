# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This project is the KatanaUI (https://katanaui.com) Component Studio. This is a project that works very much like Storybook except for Blade instead of Javascript. The KatanaUI Component Studio playground is an app that will allow developers/designers to get a live preview of their Blade components. It also supports the ability to dynamically update the props or slots and see that result in a live preview.

Allowing users to update slots or props opens up the vulnerability for a user to easily add some maliciuos/unexpected code. With that in mind, this project uses Liminal as a WASM powered Laravel app. Liminal is a **browser-based Laravel IDE** that runs PHP 8.4 entirely in WebAssembly. It provides a code editor (CodeMirror 6), site preview, Artisan terminal, and an AI agent with tool-calling capabilities — all running client-side with no backend server. Now that we have this security we can feel free to use things like eval or other unsafe strategies for rendering out blade components. Because it's sandboxed in WASM we can feel free to use any type of method or means for making it easier and more efficient to view/edit Blade components and see their real output, change prop values, change slot values, view the full code of what it looks like with those updated props/slots, and more. 

# References

### Code References
You can look at the functionality, which has been significantly built at `~/Sites/katanaui`. In there are files that will allow you to load `http://katanaui.test/components` and you will see a Storybook style component preview. It also has options down at the bottom to allow you to modify the elements prop and slot values. You can search inside of the `~/Sites/katanaui` folder for a significant amount of functionality work that we are going to port over.

### Storybook
Storybook is a front-end component previewer. This is primarily built for large JS eco-systems like React, Vue, or Svelte. You can reference some of the ideas and UI entities and port them over to this project. Remember, this project is a Blade Component previewer, similar to Storybook; however, this will allow users to view real blade components functionality/props/slots/more. 

# Visual Interface

### The UI
This is a simple interface that is very similar to Storybook, where it has a list of components on the left (also allowing those components to be split up into categories via a folder). Users can click on any component and that component will render in the main area of the app. When a component is selected there will also be options to the bottom of it. Options for specifying props, slots, and more. 

## Build Commands

```bash
npm run dev          # Start Vite dev server
npm run build        # Full build: bundle Laravel app → type-check → Vite build → split WASM
npm run bundle       # Bundle Laravel app (../liminal/app) into public/app.zip
npm run preview      # Preview production build
```

The build pipeline has three custom steps:
1. **bundle-app.js** zips the Laravel project from `../liminal/app` into `public/app.zip`
2. **vue-tsc** runs TypeScript type checking
3. **split-wasm.js** chunks WASM files >24MB for Cloudflare Pages' file size limit, creating `.wasm.part*` files with a `.wasm.json` manifest

## Architecture

### Runtime Model

PHP 8.4 runs in-browser via `@php-wasm/web-8-4`. On boot, the app downloads `app.zip`, extracts a Laravel project into a virtual filesystem (VFS), then bootstraps Laravel. All PHP execution (routing, Artisan commands) happens client-side through the WASM runtime.

### Key Composables

- **`usePhp.ts`** — Singleton managing the PHP WASM runtime. Handles boot sequence (5 phases), VFS operations (read/write/list files at `/app/` prefix), PHP code execution, Laravel HTTP routing (`navigateTo`), and Artisan commands (`runArtisan`). This is the central abstraction that all views depend on.
- **`useGlyphs.ts`** — Matrix-rain animation for the loading screen.

### Tab-Based Views (App.vue)

Four views toggled with `v-show` (kept alive, not destroyed):
- **CodeView** — CodeMirror 6 editor with file tree sidebar. Language detection by extension (PHP, Blade, HTML, JS, JSON, CSS, TS). File saves via Cmd/Ctrl+S.
- **SiteView** — Renders Laravel routes in a sandboxed iframe by executing HTTP requests through the PHP kernel.
- **TerminalView** — Interactive Artisan command runner with command history (up/down arrows).
- **AgentView** — OpenAI chat integration with SSE streaming and 4 tool functions: `read_file`, `write_file`, `list_files`, `run_artisan`. API key/model stored in localStorage.

### WASM Chunk Reassembly

`main.ts` installs a global fetch interceptor that detects `.wasm` requests, checks for a `.wasm.json` manifest, and reassembles chunked parts into a single response. This is required for Cloudflare Pages deployment.

### CORS Headers

The Vite dev server and `public/_headers` configure `Cross-Origin-Opener-Policy: same-origin` and `Cross-Origin-Embedder-Policy: credentialless` — required for SharedArrayBuffer which PHP WASM depends on.

## Code Conventions

- Vue 3 Composition API with `<script setup>` exclusively
- TypeScript strict mode; unused locals/params warnings disabled
- `shallowRef` for large objects (PHP instance), `ref` for normal state
- Components: PascalCase files. Composables: `use` prefix, camelCase files.
- Tailwind CSS v4 with stone/rose color palette
- No centralized state store — composables provide shared reactive state
