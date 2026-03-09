<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { usePhp } from '../composables/usePhp'

const { navigateTo, handleRequest, readFile, fileExists, booted } = usePhp()

watch(booted, (ready) => {
  if (ready) go()
}, { immediate: true })

const routeInput = ref('/')
const navigating = ref(false)
const srcdoc = ref(`<div style="display:flex;align-items:center;justify-content:center;height:100%;font-family:system-ui;color:#a8a29e;">Loading...</div>`)
const iframeRef = ref<HTMLIFrameElement | null>(null)

const tailwindCdn = `<script src="https://unpkg.com/@tailwindcss/browser@4"><\/script>`

// Script injected into every page to intercept link clicks, form submissions,
// and fetch/XHR requests — routing them through the WASM PHP kernel via postMessage.
const navigationInterceptor = `<script>
document.addEventListener('click', function(e) {
  var link = e.target.closest('a[href]');
  if (link) {
    var href = link.getAttribute('href');
    if (href && !href.startsWith('http') && !href.startsWith('//') && !href.startsWith('#') && !href.startsWith('javascript:')) {
      e.preventDefault();
      window.parent.postMessage({ type: 'wasm-navigate', path: href }, '*');
    }
  }
});
document.addEventListener('submit', function(e) {
  var form = e.target;
  if (form.tagName === 'FORM') {
    var action = form.getAttribute('action') || '';
    if (!action.startsWith('http') && !action.startsWith('//')) {
      e.preventDefault();
      var formData = new FormData(form);
      var params = new URLSearchParams(formData).toString();
      var method = (form.method || 'GET').toUpperCase();
      var path = action || window.location.pathname || '/';
      if (method === 'GET' && params) {
        path += (path.includes('?') ? '&' : '?') + params;
      }
      window.parent.postMessage({ type: 'wasm-navigate', path: path, method: method, body: method !== 'GET' ? params : null }, '*');
    }
  }
});

// Monkey-patch fetch() to intercept local requests and route through WASM
(function() {
  var _originalFetch = window.fetch;
  window.fetch = function(input, init) {
    var url = typeof input === 'string' ? input : (input && input.url ? input.url : '');
    // Only intercept relative URLs and localhost URLs
    var isLocal = url.startsWith('/') || url.match(/^https?:\\/\\/localhost/);
    if (!isLocal) return _originalFetch.apply(this, arguments);

    var path = url.replace(/^https?:\\/\\/localhost/, '');
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
    // Store the source so we can relay the response back
    if (!window._wasmFetchSources) window._wasmFetchSources = {};
    window._wasmFetchSources[e.data.requestId] = e.source;
  }
  if (e.data && e.data.type === 'wasm-fetch-response') {
    // Forward to the child iframe that made the request
    var source = window._wasmFetchSources && window._wasmFetchSources[e.data.requestId];
    if (source) {
      try { source.postMessage(e.data, '*'); } catch(ex) {}
      delete window._wasmFetchSources[e.data.requestId];
    }
  }
});
<\/script>`

function inlineVfsAssets(html: string): string {
  // Replace compiled CSS with source CSS for Tailwind Browser CDN processing.
  // The compiled CSS has @layer rules that conflict with the CDN's own layers,
  // so we inject the source theme/variables as <style type="text/tailwindcss">
  // and let the CDN generate all utilities on-the-fly.
  html = html.replace(
    /<link[^>]+href="\/build\/assets\/([^"]+\.css)"[^>]*\/?>/g,
    () => {
      const sourcePath = '/app/resources/css/app.css'
      if (fileExists(sourcePath)) {
        const source = readFile(sourcePath)
          .replace(/^@import\s+.*$/gm, '')
          .replace(/^@source\s+.*$/gm, '')
          .trim()
        return `<style type="text/tailwindcss">${source}</style>`
      }
      return ''
    }
  )
  // Remove build JS references (bootstrap/axios not needed in preview iframe)
  html = html.replace(
    /<script[^>]+src="\/build\/assets\/[^"]+\.js"[^>]*><\/script>/g,
    ''
  )
  // Rewrite http://localhost asset URLs to relative paths so they resolve
  // against the real dev server (via <base href>) instead of WASM's fake localhost
  html = html.replace(/https?:\/\/localhost(\/[^"'\s]+)/g, '$1')
  return html
}

function injectTailwind(html: string): string {
  // Skip if page already includes the Tailwind Browser CDN
  if (html.includes('tailwindcss/browser')) return html
  if (html.includes('<head>')) {
    return html.replace('<head>', `<head>${tailwindCdn}`)
  }
  return tailwindCdn + html
}

function injectOriginVar(html: string): string {
  const script = `<script>window.__studioOrigin="${window.location.origin}"<\/script>`
  return html.includes('<head>') ? html.replace('<head>', `<head>${script}`) : script + html
}

function injectBaseUrl(html: string): string {
  const base = `<base href="${window.location.origin}/">`
  return html.includes('<head>') ? html.replace('<head>', `<head>${base}`) : base + html
}

function injectNavigationInterceptor(html: string): string {
  if (html.includes('</body>')) {
    return html.replace('</body>', `${navigationInterceptor}</body>`)
  }
  return html + navigationInterceptor
}

let navigationId = 0

async function go(path?: string) {
  const thisNav = ++navigationId
  navigating.value = true
  const targetPath = path ?? routeInput.value

  // Update the route input to reflect current path
  routeInput.value = targetPath

  srcdoc.value = `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-family:system-ui;color:#a8a29e;">Loading ${targetPath}...</div>`

  try {
    const html = await navigateTo(targetPath)
    if (thisNav !== navigationId) return // superseded by newer navigation
    srcdoc.value = html ? injectNavigationInterceptor(injectTailwind(injectOriginVar(inlineVfsAssets(html)))) : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-family:system-ui;color:#a8a29e;">No output from ${targetPath}</div>`
  } catch (err: any) {
    if (thisNav !== navigationId) return
    srcdoc.value = `<div style="padding:2rem;font-family:system-ui;"><h2 style="color:#dc2626;margin:0 0 1rem;">Error</h2><pre style="background:#fef2f2;padding:1rem;border-radius:0.5rem;overflow:auto;color:#991b1b;font-size:0.875rem;">${err.message}</pre></div>`
    console.error(err)
  } finally {
    if (thisNav === navigationId) navigating.value = false
  }
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Enter') go()
}

// Render a route and send the HTML back to the iframe (for nested preview updates)
async function renderPreview(path: string) {
  try {
    const html = await navigateTo(path)
    const processed = html ? injectNavigationInterceptor(injectBaseUrl(injectTailwind(inlineVfsAssets(html)))) : ''
    iframeRef.value?.contentWindow?.postMessage({ type: 'preview-html', html: processed }, '*')
  } catch (err: any) {
    const errorHtml = `<div style="padding:1.5rem;font-family:system-ui;"><p style="color:#ef4444;font-weight:600;margin:0 0 0.5rem;">Render Error</p><pre style="background:#fef2f2;color:#991b1b;padding:1rem;border-radius:0.5rem;font-size:0.8rem;overflow:auto;white-space:pre-wrap;">${err.message}</pre></div>`
    iframeRef.value?.contentWindow?.postMessage({ type: 'preview-html', html: errorHtml }, '*')
  }
}

// Route a fetch request from the iframe through the WASM PHP kernel
async function handleFetchRequest(event: MessageEvent) {
  const { requestId, url, method, body, headers } = event.data
  try {
    const response = await handleRequest(url, method, body, headers)
    // Send response back to the iframe that sent the request
    iframeRef.value?.contentWindow?.postMessage({
      type: 'wasm-fetch-response',
      requestId,
      body: response.body,
      status: response.status,
      headers: response.headers
    }, '*')
  } catch (err: any) {
    iframeRef.value?.contentWindow?.postMessage({
      type: 'wasm-fetch-response',
      requestId,
      body: JSON.stringify({ error: err.message }),
      status: 500,
      headers: { 'content-type': 'application/json' }
    }, '*')
  }
}

// Listen for navigation and fetch messages from the iframe
function handleMessage(event: MessageEvent) {
  if (event.data?.type === 'wasm-render-preview' && event.data.path) {
    renderPreview(event.data.path)
  } else if (event.data?.type === 'wasm-navigate' && event.data.path) {
    go(event.data.path)
  } else if (event.data?.type === 'wasm-fetch' && event.data.requestId) {
    handleFetchRequest(event)
  }
}

defineExpose({ routeInput, go, navigating })

onMounted(() => {
  window.addEventListener('message', handleMessage)
})

onUnmounted(() => {
  window.removeEventListener('message', handleMessage)
})
</script>

<template>
  <div class="flex-1 flex flex-col min-h-0">
    <div class="flex-1 bg-stone-100 dark:bg-stone-950 min-h-0">
      <iframe
        ref="iframeRef"
        :srcdoc="srcdoc"
        class="w-full h-full bg-white"
      ></iframe>
    </div>
  </div>
</template>
