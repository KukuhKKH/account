<script setup lang="ts">
import { computed } from 'vue'
import { useAuth } from '~/composables/useAuth'
import type { SidebarMenuItem } from '~/types/navigation'
import sidebarData from '~/config/sidebar.json'
import UserAvatar from '~/components/UserAvatar.vue'
import {
  ShieldCheck,
  LayoutDashboard,
  Users,
  Server,
  FileText,
  Bot,
  LogOut,
  ChevronRight,
  UserCheck
} from 'lucide-vue-next'

const {
  user,
  isSuperadmin,
  isAdminAccount,
  canManageUsers,
  redirectToLogout
} = useAuth()

const route = useRoute()

// Map string icon name ke komponen Lucide Vue
const iconMap: Record<string, any> = {
  LayoutDashboard,
  Users,
  Server,
  FileText,
  Bot,
  UserCheck
}

const menuItems = sidebarData as SidebarMenuItem[]

// Filter menu items berdasarkan permission pengguna
const visibleMenuItems = computed(() => {
  return menuItems.filter((item) => {
    if (!item.permission || item.permission === 'all') return true
    if (item.permission === 'canManageUsers') return canManageUsers.value
    if (item.permission === 'isSuperadmin') return isSuperadmin.value
    if (item.permission === 'isAdminAccount') return isAdminAccount.value
    return false
  })
})

function isActive(path: string): boolean {
  if (path === '/dashboard') {
    return route.path === '/dashboard' || route.path === '/dashboard/'
  }
  return route.path.startsWith(path)
}
</script>

<template>
  <aside class="w-64 shrink-0 hidden lg:flex flex-col justify-between border-r border-slate-200/80 dark:border-slate-800/80 bg-white/70 dark:bg-slate-950/70 backdrop-blur-xl p-4 transition-colors z-20">
    <div class="space-y-6">
      
      <!-- Brand & Badge -->
      <NuxtLink to="/" class="flex items-center gap-2.5 px-2 group">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-sky-500 p-0.5 shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
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
      <div class="p-3 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50">
        <div class="flex items-center justify-between text-xs mb-2">
          <span class="text-slate-500 dark:text-slate-400 font-mono text-[10px]">CURRENT ROLE</span>
          <Tag
            :value="user?.role"
            :severity="isSuperadmin ? 'danger' : (isAdminAccount ? 'info' : 'secondary')"
            class="!text-[10px]"
          />
        </div>
        <div class="flex items-center gap-2.5">
          <UserAvatar
            :name="user?.name"
            :email="user?.email"
            :avatar="user?.avatarUrl"
            :role="user?.role"
            size="sm"
            class="shadow-xs"
          />
          <div class="min-w-0">
            <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
              {{ user?.name }}
            </p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono truncate">
              {{ user?.email }}
            </p>
          </div>
        </div>
      </div>

      <!-- Navigation Menus (Driven by JSON configuration) -->
      <nav class="space-y-1 text-xs font-medium">
        <NuxtLink
          v-for="item in visibleMenuItems"
          :key="item.id"
          :to="item.path"
          class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all text-left group"
          :class="isActive(item.path)
            ? 'bg-indigo-600 text-white font-semibold shadow-md shadow-indigo-600/20' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'"
        >
          <div class="flex items-center gap-2.5">
            <component
              :is="iconMap[item.icon]"
              v-if="iconMap[item.icon]"
              class="w-4 h-4 transition-transform group-hover:scale-110"
              :class="item.iconClass"
            />
            <span>{{ item.title }}</span>
          </div>

          <!-- Badge Pill -->
          <span
            v-if="item.badge"
            class="text-[10px] font-mono px-1.5 py-0.5 rounded transition-colors"
            :class="{
              'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300': item.badge.variant === 'rbac' || !item.badge.variant,
              'bg-rose-500/10 text-rose-500 font-bold': item.badge.variant === 'root',
              'bg-amber-500/10 text-amber-500': item.badge.variant === 'warning',
              'bg-sky-500/10 text-sky-500': item.badge.variant === 'info'
            }"
          >
            {{ item.badge.text }}
          </span>
        </NuxtLink>
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
        @click="redirectToLogout()"
        class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
      >
        <LogOut class="w-4 h-4" />
        <span>Keluar (Logout)</span>
      </button>
    </div>
  </aside>
</template>
