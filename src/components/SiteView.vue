<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { usePhp } from '../composables/usePhp'

const { navigateTo, readFile, fileExists, booted } = usePhp()

watch(booted, (ready) => {
  if (ready) go()
}, { immediate: true })

const routeInput = ref('/')
const navigating = ref(false)
const srcdoc = ref(`<div style="display:flex;align-items:center;justify-content:center;height:100%;font-family:system-ui;color:#a8a29e;">Loading...</div>`)
const iframeRef = ref<HTMLIFrameElement | null>(null)

const tailwindCdn = `<script src="https://unpkg.com/@tailwindcss/browser@4"><\/script>`

// Script injected into every page to intercept link clicks and form submissions
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
    srcdoc.value = html ? injectNavigationInterceptor(injectTailwind(inlineVfsAssets(html))) : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-family:system-ui;color:#a8a29e;">No output from ${targetPath}</div>`
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
    const processed = html ? injectTailwind(inlineVfsAssets(html)) : ''
    iframeRef.value?.contentWindow?.postMessage({ type: 'preview-html', html: processed }, '*')
  } catch (err: any) {
    const errorHtml = `<div style="padding:1.5rem;font-family:system-ui;"><p style="color:#ef4444;font-weight:600;margin:0 0 0.5rem;">Render Error</p><pre style="background:#fef2f2;color:#991b1b;padding:1rem;border-radius:0.5rem;font-size:0.8rem;overflow:auto;white-space:pre-wrap;">${err.message}</pre></div>`
    iframeRef.value?.contentWindow?.postMessage({ type: 'preview-html', html: errorHtml }, '*')
  }
}

// Listen for navigation messages from the iframe
function handleMessage(event: MessageEvent) {
  if (event.data?.type === 'wasm-render-preview' && event.data.path) {
    renderPreview(event.data.path)
  } else if (event.data?.type === 'wasm-navigate' && event.data.path) {
    go(event.data.path)
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
