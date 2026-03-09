<script setup lang="ts">
import { ref, watch, useTemplateRef, nextTick } from 'vue'
import HelpModal from './HelpModal.vue'

const props = defineProps<{
  route: string
  navigating: boolean
}>()

const emit = defineEmits<{
  navigate: [path: string]
}>()

const helpOpen = ref(false)
const editing = ref(false)
const editValue = ref('')
const routeInput = useTemplateRef('route-input')

watch(() => props.route, (val) => {
  if (!editing.value) editValue.value = val
})

function startEdit() {
  editValue.value = props.route
  editing.value = true
  nextTick(() => {
    routeInput.value?.focus()
  })
}

function submitRoute() {
  editing.value = false
  emit('navigate', editValue.value)
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Enter') submitRoute()
  if (e.key === 'Escape') editing.value = false
}
</script>

<template>
  <header class="sticky top-0 z-50 bg-white/80 dark:bg-stone-900/80 backdrop-blur border-b border-stone-200 dark:border-stone-700 shrink-0">
    <div class="px-4 py-2.5 flex items-center gap-4">
      <!-- Logo -->
      <div class="flex items-center gap-2.5 shrink-0">
        <svg class="w-5 h-5 text-stone-800 dark:text-stone-100 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 96" fill="none"><path fill="currentColor" d="M99.921 22.892c-.016-.053-.038-.101-.058-.152a2.09 2.09 0 0 0-.158-.326 1.888 1.888 0 0 0-.349-.412c-.041-.037-.078-.075-.121-.108-.02-.015-.035-.036-.057-.05-.13-.09-.27-.163-.418-.217l-26.044-9.97V10c0-.406-.045-.8-.133-1.183-.175-.764-.519-1.481-1.008-2.151a7.986 7.986 0 0 0-.84-.968 10.166 10.166 0 0 0-.725-.646c-.099-.081-.207-.155-.31-.232-.166-.126-.329-.251-.505-.371-.142-.097-.291-.189-.439-.282-.15-.096-.3-.19-.453-.28-.18-.105-.364-.206-.549-.303-.136-.072-.271-.145-.41-.215a22.151 22.151 0 0 0-1.784-.798l-.291-.113c-.292-.11-.586-.219-.888-.323-.068-.023-.137-.044-.205-.068-.335-.111-.672-.22-1.021-.324C61.019 1.107 58.66.641 56.217.352a51.171 51.171 0 0 0-2.979-.263h-.004c-1-.06-2-.089-3.001-.089h-.003c-9.536 0-19.599 2.684-21.965 7.706A5.324 5.324 0 0 0 27.747 10v1.175L1.242 21.164a1.893 1.893 0 0 0-.417.215c-.02.013-.033.031-.053.046-.046.034-.084.075-.126.113-.089.08-.17.164-.243.257-.108.143-.196.3-.261.468-.021.055-.044.106-.062.163a1.94 1.94 0 0 0-.078.499l-.002.02v52.263c0 .789.487 1.497 1.224 1.779l48.325 18.497c.017.007.035.004.052.01.204.072.414.116.629.116.213 0 .421-.043.622-.112.017-.006.033-.003.051-.01l47.864-18.035A1.905 1.905 0 0 0 100 75.67V23.412c0-.179-.032-.352-.079-.52ZM31.642 9.516a2.26 2.26 0 0 1 .219-.449c.032-.053.077-.105.115-.158.069-.1.142-.2.229-.301.057-.065.126-.131.19-.197.087-.088.176-.176.276-.264a9.45 9.45 0 0 1 1.259-.9c.146-.089.299-.179.461-.267.099-.054.203-.106.306-.161.19-.098.382-.195.59-.292.072-.034.149-.067.225-.1 2.062-.929 4.876-1.728 8.298-2.19l.034-.005c.491-.065.992-.125 1.508-.174l.06-.007a44.516 44.516 0 0 1 1.586-.128h.005a49.896 49.896 0 0 1 6.624.004c1.817.122 3.493.341 5.026.627l.318.061c1.213.236 2.329.518 3.343.829l.307.096c.491.158.964.321 1.402.491l.019.008c.424.166.815.339 1.188.514l.271.13c.362.179.707.36 1.015.543l.027.019c.344.208.675.437.99.687.234.188.45.375.627.562.489.521.745 1.035.745 1.507 0 2.522-7.273 6.187-18.672 6.187-.843 0-1.654-.026-2.45-.064-.239-.01-.47-.027-.704-.042a49.524 49.524 0 0 1-1.673-.133c-.225-.022-.449-.043-.67-.068a41.958 41.958 0 0 1-1.859-.251c-.08-.013-.165-.022-.244-.036a36.654 36.654 0 0 1-2.02-.39c-.094-.02-.182-.043-.273-.064-.633-.145-1.26-.31-1.883-.493-2.702-.8-4.688-1.815-5.814-2.826-.044-.04-.086-.078-.125-.117a4.528 4.528 0 0 1-.286-.287 3.636 3.636 0 0 1-.301-.378c-.038-.055-.064-.11-.096-.164a2.39 2.39 0 0 1-.202-.447 1.547 1.547 0 0 1-.074-.425c0-.166.029-.33.083-.487Zm37.263 6.213v6.526c0 2.521-7.271 6.185-18.669 6.185v-8.442c7.049 0 14.366-1.474 18.669-4.269Zm-41.158-.48v7.006c0 6.494 11.584 9.999 22.483 9.999 10.902 0 22.487-3.505 22.487-9.999v-6.519l20.009 7.66-23.27 8.77-19.22 7.244-17.323-6.632-25.639-9.814 20.473-7.715Zm68.442 59.102L52.136 90.95V42.767l15.686-5.911 28.367-10.689v48.184Z"/></svg>
        <span class="text-sm font-semibold text-stone-800 dark:text-stone-100 tracking-tight">component <span class="font-normal text-stone-500 dark:text-stone-400">studio</span></span>
      </div>

      <!-- Route display / editor -->
      <div class="flex-1 flex items-center min-w-0 w-full">
        <!-- Read-only mode -->
        <div v-if="!editing" @click="startEdit" class="flex cursor-pointer items-center gap-1.5 justify-between bg-stone-100 hover:bg-stone-200 pr-2 pl-3 group rounded-full h-7 min-w-0 w-full">
          <span class="text-xs font-mono text-stone-400 dark:text-stone-500 truncate">{{ route }}</span>
          <button
            class="w-5 h-5 flex items-center justify-center rounded text-stone-400 dark:text-stone-500 group-hover:text-stone-600 dark:group-hover:text-stone-300 group-hover:bg-stone-100 dark:group-hover:bg-stone-800 transition-colors cursor-pointer shrink-0"
            title="Edit route"
          >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
          </button>
        </div>

        <!-- Edit mode -->
        <div v-else class="flex min-h-7 items-center gap-1.5 relative flex-1 min-w-0">
          <input
            v-model="editValue"
            type="text"
            spellcheck="false"
            ref="route-input"
            autofocus
            class="flex-1 px-2 py-1 text-xs font-mono bg-white dark:bg-stone-800 border border-stone-300 dark:border-stone-600 rounded-full pr-10 text-stone-700 dark:text-stone-200 outline-none focus:border-rose-400 dark:focus:border-rose-500 min-w-0"
            :disabled="navigating"
            @keydown="onKeydown"
            @blur="editing = false"
          />
          <button
            :disabled="navigating"
            class="px-2 py-0.5 text-xs absolute right-1 font-medium text-white rounded-full disabled:opacity-40 disabled:cursor-not-allowed shrink-0"
            @mousedown.prevent="submitRoute"
          >Go</button>
        </div>
      </div>

      <!-- Right side -->
      <div class="flex items-center gap-3 shrink-0">
        <span class="text-xs text-stone-400 dark:text-stone-500 font-mono hidden">Laravel 12 / PHP 8.4</span>
        <a
          href="https://katanaui.com"
          target="_blank"
          class="flex items-center gap-1.5 pl-1.5 pr-2.5 py-1 text-xs font-medium text-stone-900 dark:text-stone-100 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-full hover:border-stone-300 dark:hover:border-stone-600 hover:text-stone-700 dark:hover:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700 transition-all no-underline"
        >
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 98" fill="none">
            <path fill="currentColor" d="M87.34 19.483 71.297 35.74c-1.749-1.735-3.39-3.357-5.023-4.987-.54-.54-1.224-1.007-1.877-.503a13.138 13.138 0 0 0-2.597 2.596c-.409.56-.147 1.37.457 1.92 1.691 1.541 3.37 3.096 5.149 4.733-2.124 2.015-3.93 3.758-5.768 5.465-3.569 3.313-7.127 6.64-10.745 9.9a629.017 629.017 0 0 1-11.028 9.703 628.932 628.932 0 0 1-11.595 9.71c-3.95 3.216-7.985 6.329-11.966 9.508-.435.347-.676.27-1.034-.084C7.83 76.36 2.828 67.704.898 57.353c-.677-3.63-1.03-7.282-.852-10.96.498-10.317 3.72-19.667 10.133-27.843 6.397-8.154 14.605-13.63 24.517-16.58A48.33 48.33 0 0 1 51.906.13c10.23.702 19.355 4.336 27.317 10.808a46.99 46.99 0 0 1 8.119 8.545ZM47.982 28.86c-.03-.002-.075-.018-.086-.005-.098.112-.18.237-.284.343-2.239 2.303-4.87 3.544-8.172 3.196-4.178-.44-7.984-4.033-8.184-9.042-.138-3.489 1.372-6.295 4.095-8.442.78-.616 1.627-1.147 2.466-1.732-2.261-1.119-7.873.096-11 3.538-3.787 4.17-4.407 10.616-1.414 15.56a12.598 12.598 0 0 0 13.992 5.589c4.936-1.35 8.21-5.5 8.587-9.005ZM30.189 93.195c16.777-13.193 32.19-27.793 46.724-43.042 1.143 1.054 2.232 2.058 3.32 3.064.352.362.722.705 1.11 1.027.834.619 1.552.549 2.293-.178.51-.499 1.015-1.002 1.503-1.522.772-.823.776-1.799-.04-2.589-1.493-1.444-3.018-2.855-4.616-4.36l13.39-13.833c5.661 14.65 4.307 35.94-12.087 51.949-15.141 14.784-36.977 16.484-51.597 9.484ZM71.148 44.291l3.485 3.213c-1.993 2.069-3.864 4.096-5.827 6.03-5.267 5.194-10.494 10.432-15.888 15.49-4.414 4.141-9.01 8.092-13.593 12.048-3.996 3.448-8.016 6.876-12.162 10.14-2.858 2.25-6.03 4.026-9.661 4.828-4.035.892-7.98.574-11.878-.707a5.967 5.967 0 0 1-1.02-.4 48.15 48.15 0 0 0 10.422-3.245c4.464-1.934 8.415-4.687 12.141-7.761 5.147-4.247 10.31-8.48 15.323-12.882 5.6-4.918 11.08-9.972 16.532-15.054 4.147-3.867 8.168-7.871 12.126-11.7ZM97.024 25.52l-5.981-5.733c1.018-.994 1.838-1.918 2.786-2.685.89-.721 2.163-.6 3.004.181a37.324 37.324 0 0 1 2.434 2.462c.989 1.107.962 2.371.01 3.52-.215.281-.445.552-.689.81-.487.475-.999.925-1.564 1.444ZM84.183 32.03l.531 3.924-4.08-.337-.25-3.836 3.799.249ZM90.51 30.038c-1.261-.124-2.427-.226-3.586-.37a.57.57 0 0 1-.358-.403c-.114-1.075-.186-2.155-.282-3.37l4.038.201.189 3.942ZM75.282 41.051l-.244-3.51 3.818.234.356 3.873-3.93-.597Z"/>
          </svg>
          Home
          <svg class="w-3 h-3 opacity-50 group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
          </svg>
        </a>
        <button
          class="w-5 h-5 flex items-center justify-center hover:bg-stone-200 rounded-full border border-stone-300 dark:border-stone-600 text-stone-400 dark:text-stone-500 hover:border-stone-400 dark:hover:border-stone-500 hover:text-stone-600 dark:hover:text-stone-300 transition-colors cursor-pointer text-xs font-semibold leading-none"
          aria-label="Help"
          @click="helpOpen = true"
        >?</button>
      </div>
    </div>
  </header>

  <HelpModal v-if="helpOpen" @close="helpOpen = false" />
</template>
