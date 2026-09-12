<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import {
  ShieldCheck,
  LayoutDashboard,
  Users,
  Server,
  KeyRound,
  FileText,
  Settings,
  Bot,
  LogOut,
  ChevronRight,
  Lock,
  UserCheck
} from 'lucide-vue-next'

const authStore = useAuthStore()
const route = useRoute()

defineProps<{
  activeTab: string
}>()

const emit = defineEmits<{
  (e: 'update:activeTab', tab: string): void
}>()
</script>

<template>
  <aside class="w-64 shrink-0 hidden lg:flex flex-col justify-between border-r border-slate-200/80 dark:border-slate-800/80 bg-white/60 dark:bg-slate-950/60 backdrop-blur-xl p-4 transition-colors">
    <div class="space-y-6">
      
      <!-- Brand & Badge -->
      <NuxtLink to="/" class="flex items-center gap-2.5 px-2">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-sky-500 p-0.5 shadow-md shadow-indigo-500/20">
          <div class="w-full h-full bg-white dark:bg-slate-900 rounded-[10px] flex items-center justify-center">
            <ShieldCheck class="w-5 h-5 text-indigo-600 dark:text-sky-400" />
          </div>
        </div>
        <div>
          <span class="font-extrabold text-sm tracking-tight text-slate-900 dark:text-white block">
            BangLipai Portal
          </span>
          <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
            Access Dashboard
          </span>
        </div>
      </NuxtLink>

      <!-- Role Badge Summary -->
      <div class="p-3 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50">
        <div class="flex items-center justify-between text-xs">
          <span class="text-slate-500 dark:text-slate-400 font-mono text-[10px]">CURRENT ROLE</span>
          <Tag
            :value="authStore.currentUser?.role"
            :severity="authStore.isSuperadmin ? 'danger' : (authStore.isAdminAccount ? 'info' : 'secondary')"
            class="!text-[10px]"
          />
        </div>
        <p class="text-xs font-bold text-slate-900 dark:text-white mt-1 truncate">
          {{ authStore.currentUser?.name }}
        </p>
      </div>

      <!-- Navigation Menus -->
      <nav class="space-y-1 text-xs font-medium">
        
        <!-- Tab: Overview (All Roles) -->
        <button
          @click="emit('update:activeTab', 'overview')"
          class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all cursor-pointer text-left"
          :class="activeTab === 'overview' 
            ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'"
        >
          <div class="flex items-center gap-2.5">
            <LayoutDashboard class="w-4 h-4" />
            <span>Ringkasan (Overview)</span>
          </div>
        </button>

        <!-- Tab: User Management (Superadmin & Admin Account only) -->
        <button
          v-if="authStore.canManageUsers"
          @click="emit('update:activeTab', 'users')"
          class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all cursor-pointer text-left"
          :class="activeTab === 'users' 
            ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'"
        >
          <div class="flex items-center gap-2.5">
            <Users class="w-4 h-4" />
            <span>Manajemen Pengguna</span>
          </div>
          <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
            RBAC
          </span>
        </button>

        <!-- Tab: Cluster Nodes & Topology (Superadmin ONLY) -->
        <button
          v-if="authStore.isSuperadmin"
          @click="emit('update:activeTab', 'cluster')"
          class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all cursor-pointer text-left"
          :class="activeTab === 'cluster' 
            ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'"
        >
          <div class="flex items-center gap-2.5">
            <Server class="w-4 h-4" />
            <span>Klaster & Subnet</span>
          </div>
          <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-500 font-bold">
            Root
          </span>
        </button>

        <!-- Tab: Security & Audit Logs (All roles, scoped) -->
        <button
          @click="emit('update:activeTab', 'audit')"
          class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all cursor-pointer text-left"
          :class="activeTab === 'audit' 
            ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'"
        >
          <div class="flex items-center gap-2.5">
            <FileText class="w-4 h-4" />
            <span>Log Aktivitas & Audit</span>
          </div>
        </button>

        <!-- Tab: AI Telemetry Agent Karina (Superadmin only) -->
        <button
          v-if="authStore.isSuperadmin"
          @click="emit('update:activeTab', 'ai_ops')"
          class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all cursor-pointer text-left"
          :class="activeTab === 'ai_ops' 
            ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'"
        >
          <div class="flex items-center gap-2.5">
            <Bot class="w-4 h-4 text-rose-400" />
            <span>Karina AI Ops Center</span>
          </div>
        </button>

        <!-- Tab: Personal Profile & Security (Available for all, primary for User role) -->
        <button
          @click="emit('update:activeTab', 'profile')"
          class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all cursor-pointer text-left"
          :class="activeTab === 'profile' 
            ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'"
        >
          <div class="flex items-center gap-2.5">
            <UserCheck class="w-4 h-4" />
            <span>Profil & Kunci Keamanan</span>
          </div>
        </button>

      </nav>
    </div>

    <!-- Bottom Actions -->
    <div class="space-y-2 border-t border-slate-200/80 dark:border-slate-800/80 pt-4">
      <NuxtLink
        to="/"
        class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
      >
        <ChevronRight class="w-4 h-4 rotate-180" />
        <span>Kembali ke Beranda</span>
      </NuxtLink>

      <button
        @click="authStore.logout(); navigateTo('/')"
        class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
      >
        <LogOut class="w-4 h-4" />
        <span>Keluar (Logout)</span>
      </button>
    </div>
  </aside>
</template>
