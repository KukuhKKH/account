<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useThemeStore } from '~/stores/theme'
import {
  Sun,
  Moon,
  ShieldCheck,
  Bell,
  RefreshCw,
  LogOut
} from 'lucide-vue-next'

const authStore = useAuthStore()
const themeStore = useThemeStore()

defineProps<{
  title: string
  subtitle?: string
}>()
</script>

<template>
  <header class="h-16 shrink-0 border-b border-slate-200/80 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/50 backdrop-blur-xl px-4 sm:px-6 flex items-center justify-between transition-colors">
    
    <!-- Title & Breadcrumb -->
    <div>
      <div class="flex items-center gap-2">
        <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">
          {{ title }}
        </h1>
        <Tag
          :value="authStore.currentUser?.role"
          :severity="authStore.isSuperadmin ? 'danger' : (authStore.isAdminAccount ? 'info' : 'secondary')"
          class="!text-[10px]"
        />
      </div>
      <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 hidden sm:block -mt-0.5">
        {{ subtitle }}
      </p>
    </div>

    <!-- Header Right Actions -->
    <div class="flex items-center gap-2 sm:gap-3">
      
      <!-- Theme Switcher -->
      <button
        @click="themeStore.toggleTheme()"
        class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        aria-label="Toggle Theme"
      >
        <Sun v-if="themeStore.isDarkMode" class="w-4 h-4 text-amber-400" />
        <Moon v-else class="w-4 h-4 text-indigo-600" />
      </button>

      <!-- User avatar button -->
      <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">
          {{ authStore.currentUser?.name.charAt(0) || 'U' }}
        </div>
        <div class="hidden sm:block text-left text-xs">
          <p class="font-bold text-slate-900 dark:text-white leading-tight truncate max-w-[120px]">
            {{ authStore.currentUser?.name }}
          </p>
          <p class="text-[10px] text-slate-500 font-mono">{{ authStore.currentUser?.email }}</p>
        </div>
      </div>

    </div>
  </header>
</template>
