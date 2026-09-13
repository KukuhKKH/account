<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useThemeStore } from '~/stores/theme'
import SsoAuthModal from '~/components/modals/SsoAuthModal.vue'
import NodeDetailModal from '~/components/modals/NodeDetailModal.vue'

const authStore = useAuthStore()
const themeStore = useThemeStore()

onMounted(async () => {
  themeStore.initTheme()
  await authStore.initSession(false)
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 font-sans selection:bg-indigo-500 selection:text-white transition-colors duration-200 flex">
    
    <!-- Ambient Background Glow & Grids -->
    <div class="fixed inset-0 pointer-events-none bg-grid-pattern opacity-60 dark:opacity-40 z-0"></div>
    <div class="fixed top-0 right-1/4 w-[800px] h-[400px] bg-indigo-500/10 dark:bg-indigo-600/15 blur-[140px] rounded-full pointer-events-none z-0"></div>

    <!-- Main Content Slot -->
    <div class="relative z-10 flex-1 flex flex-col min-w-0">
      <slot />
    </div>

    <!-- Global Modals -->
    <SsoAuthModal />
    <NodeDetailModal />
  </div>
</template>
