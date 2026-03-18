<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePhp } from './composables/usePhp'
import { useShareUrl } from './composables/useShareUrl'
import { useTheme } from './composables/useTheme'
import LoadingOverlay from './components/LoadingOverlay.vue'
import AppHeader from './components/AppHeader.vue'
import TabBar from './components/TabBar.vue'
import SiteView from './components/SiteView.vue'
import CodeView from './components/CodeView.vue'
import TerminalView from './components/TerminalView.vue'
import AgentView from './components/AgentView.vue'
import ToolsView from './components/ToolsView.vue'

const activeTab = ref<'site' | 'code' | 'terminal' | 'agent' | 'tools'>('site')
const loading = ref(true)
const loadingFailed = ref(false)
const siteViewRef = ref<InstanceType<typeof SiteView> | null>(null)

const currentRoute = computed(() => (siteViewRef.value as any)?.routeInput ?? '/')
const isNavigating = computed(() => (siteViewRef.value as any)?.navigating ?? false)

function handleNavigate(path: string) {
  ;(siteViewRef.value as any)?.go(path)
}

const { boot, booted, bootProgress, bootStatus } = usePhp()
const { captureFromUrl, applyPendingPayload } = useShareUrl()
useTheme()

const hasSharePayload = captureFromUrl()

onMounted(async () => {
  try {
    await boot()
    if (hasSharePayload) await applyPendingPayload()
    setTimeout(() => {
      loading.value = false
    }, 400)
  } catch (err: any) {
    loadingFailed.value = true
    console.error(err)
  }
})
</script>

<template>
  <div class="bg-stone-50 dark:bg-stone-950 text-stone-700 dark:text-stone-200 antialiased fixed inset-0 flex flex-col overflow-hidden">
    <LoadingOverlay
      v-if="loading"
      :progress="bootProgress"
      :status="bootStatus"
      :failed="loadingFailed"
    />

    <!-- Top accent border -->
    <div class="h-1.5 bg-rose-500 shrink-0 hidden"></div>

    <div class="w-full h-full p-1 pb-0 bg-linear-to-br from-stone-100 via-stone-50 to-stone-200 flex flex-col">
    <div class="rounded-t-lg flex border border-stone-200 border-b-0 flex-col overflow-hidden h-full">

    <AppHeader :route="currentRoute" :navigating="isNavigating" @navigate="handleNavigate" />
    <TabBar :active-tab="activeTab" @update:active-tab="activeTab = $event" />

    <SiteView ref="siteViewRef" v-show="activeTab === 'site'" />
    <CodeView v-show="activeTab === 'code'" />
    <TerminalView v-show="activeTab === 'terminal'" />
    <AgentView v-show="activeTab === 'agent'" />
    <ToolsView v-show="activeTab === 'tools'" />
    </div>
    </div>
  </div>
</template>
