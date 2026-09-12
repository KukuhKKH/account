<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useThemeStore } from '~/stores/theme'
import { useClusterStore } from '~/stores/cluster'
import {
  ShieldCheck,
  Zap,
  Sun,
  Moon,
  Users,
  LogOut,
  LayoutDashboard,
  Menu,
  X,
  ChevronDown
} from 'lucide-vue-next'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const clusterStore = useClusterStore()

const mobileMenuOpen = ref(false)
const userDropdownOpen = ref(false)

const route = useRoute()
const isHomePage = computed(() => route.path === '/')

function closeDropdowns() {
  userDropdownOpen.value = false
  mobileMenuOpen.value = false
}
</script>

<template>
  <header class="sticky top-0 z-40 w-full backdrop-blur-xl bg-white/70 dark:bg-slate-950/70 border-b border-slate-200/80 dark:border-slate-800/80 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      
      <!-- Brand & Title -->
      <NuxtLink to="/" class="flex items-center gap-3 group" @click="closeDropdowns">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-sky-500 to-emerald-400 p-[2px] shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform duration-300">
          <div class="w-full h-full bg-white dark:bg-slate-900 rounded-[10px] flex items-center justify-center">
            <ShieldCheck class="w-5 h-5 text-indigo-600 dark:text-sky-400" />
          </div>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <span class="font-extrabold text-base sm:text-lg tracking-tight bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-700 dark:from-white dark:via-sky-200 dark:to-indigo-300 bg-clip-text text-transparent">
              BangLipai Identity
            </span>
            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/50">
              v2.5 SSO
            </span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 -mt-0.5 hidden sm:block">
            Centralized Access Engine & BFF Security Core
          </p>
        </div>
      </NuxtLink>

      <!-- Desktop Nav Navigation (when on Home page) -->
      <nav v-if="isHomePage" class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600 dark:text-slate-300">
        <a href="#services" class="hover:text-indigo-600 dark:hover:text-sky-400 transition-colors">Layanan</a>
        <a href="#topology" class="hover:text-indigo-600 dark:hover:text-sky-400 transition-colors">Topologi Klaster</a>
        <a href="#iam" class="hover:text-indigo-600 dark:hover:text-sky-400 transition-colors">IAM & Benchmark</a>
      </nav>

      <!-- Right Action Controls -->
      <div class="flex items-center gap-2 sm:gap-3">
        
        <!-- Live Ping Latency Chip -->
        <button
          @click="clusterStore.measureLivePing()"
          :disabled="clusterStore.isPinging"
          class="hidden sm:inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-mono bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-all cursor-pointer"
          title="Klik untuk tes live round-trip latency ke backend"
        >
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>{{ clusterStore.pingLatency !== null ? `${clusterStore.pingLatency}ms` : '< 2ms' }}</span>
          <Zap class="w-3 h-3 text-emerald-600 dark:text-emerald-400" :class="{ 'animate-spin': clusterStore.isPinging }" />
        </button>

        <!-- Dark / Light Theme Toggle -->
        <button
          @click="themeStore.toggleTheme()"
          class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-all cursor-pointer"
          :title="themeStore.isDarkMode ? 'Beralih ke Light Mode' : 'Beralih ke Dark Mode'"
          aria-label="Toggle Theme"
        >
          <Sun v-if="themeStore.isDarkMode" class="w-4 h-4 text-amber-400" />
          <Moon v-else class="w-4 h-4 text-indigo-600" />
        </button>

        <!-- Dashboard Link (if logged in) -->
        <NuxtLink
          v-if="authStore.isLoggedIn"
          to="/dashboard"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer shadow-xs"
          :class="route.path.startsWith('/dashboard') 
            ? 'bg-indigo-600 text-white border-indigo-600 shadow-indigo-600/30' 
            : 'bg-indigo-50 dark:bg-indigo-950/60 border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100'"
        >
          <LayoutDashboard class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">Dashboard</span>
        </NuxtLink>

        <!-- SSO Portal Button / User Menu -->
        <div v-if="authStore.isLoggedIn" class="relative">
          <button
            @click="userDropdownOpen = !userDropdownOpen"
            class="flex items-center gap-2 p-1 sm:px-2.5 sm:py-1 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/50 dark:bg-indigo-950/40 text-xs font-medium text-slate-800 dark:text-slate-200 cursor-pointer hover:bg-indigo-100/60 dark:hover:bg-indigo-900/40 transition-all"
          >
            <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
              {{ authStore.currentUser?.name.charAt(0) || 'U' }}
            </div>
            <div class="text-left hidden sm:block">
              <p class="font-semibold text-xs leading-none">{{ authStore.currentUser?.name }}</p>
              <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight font-mono mt-0.5">
                {{ authStore.currentUser?.role }}
              </p>
            </div>
            <ChevronDown class="w-3.5 h-3.5 text-slate-400 hidden sm:block" />
          </button>

          <!-- User Dropdown Menu -->
          <div
            v-if="userDropdownOpen"
            @click="userDropdownOpen = false"
            class="absolute right-0 mt-2 w-56 rounded-2xl glass-panel p-2 shadow-2xl z-50 animate-in fade-in zoom-in-95 duration-150"
          >
            <div class="p-2 border-b border-slate-200/80 dark:border-slate-800/80">
              <p class="text-xs font-bold text-slate-900 dark:text-white">{{ authStore.currentUser?.name }}</p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ authStore.currentUser?.email }}</p>
              <Tag 
                :value="authStore.currentUser?.role" 
                :severity="authStore.isSuperadmin ? 'danger' : (authStore.isAdminAccount ? 'info' : 'secondary')" 
                class="!text-[10px] mt-1.5" 
              />
            </div>
            
            <div class="py-1 space-y-0.5">
              <NuxtLink
                to="/dashboard"
                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-slate-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 hover:text-indigo-600 transition-colors"
              >
                <LayoutDashboard class="w-3.5 h-3.5" />
                <span>Control Dashboard</span>
              </NuxtLink>

              <button
                @click="authStore.openSsoModal()"
                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-left"
              >
                <Users class="w-3.5 h-3.5" />
                <span>Switch Role / Portal</span>
              </button>

              <button
                @click="authStore.logout()"
                class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors text-left"
              >
                <LogOut class="w-3.5 h-3.5" />
                <span>Keluar (Logout)</span>
              </button>
            </div>
          </div>
        </div>

        <button
          v-else
          @click="authStore.openSsoModal()"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-sky-600 hover:from-indigo-500 hover:to-sky-500 text-white text-xs font-semibold shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all cursor-pointer"
        >
          <Users class="w-3.5 h-3.5" />
          <span>Masuk SSO</span>
        </button>

        <!-- Mobile Hamburger -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300"
          aria-label="Toggle Menu"
        >
          <Menu v-if="!mobileMenuOpen" class="w-5 h-5" />
          <X v-else class="w-5 h-5" />
        </button>

      </div>
    </div>

    <!-- Mobile Nav Menu Dropdown -->
    <div v-if="mobileMenuOpen" class="md:hidden border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-950/95 backdrop-blur-2xl p-4 space-y-3">
      <nav class="flex flex-col space-y-2 text-sm font-medium text-slate-700 dark:text-slate-300">
        <a href="#services" @click="mobileMenuOpen = false" class="py-2 px-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Layanan Ekosistem</a>
        <a href="#topology" @click="mobileMenuOpen = false" class="py-2 px-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">Topologi Klaster</a>
        <a href="#iam" @click="mobileMenuOpen = false" class="py-2 px-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">IAM & Benchmark</a>
        <NuxtLink v-if="authStore.isLoggedIn" to="/dashboard" @click="mobileMenuOpen = false" class="py-2 px-3 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-300 font-semibold">
          Buka Dashboard
        </NuxtLink>
      </nav>
    </div>
  </header>
</template>
