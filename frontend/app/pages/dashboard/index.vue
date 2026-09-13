<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import {
  Server,
  Zap,
  Users,
  Bot,
  ArrowRight,
  ShieldCheck,
  Activity,
  Layers,
  Lock
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

const { user, isSuperadmin, canManageUsers } = useAuth()
</script>

<template>
  <div class="flex-1 flex flex-col min-w-0">
    <DashboardHeader
      title="Control Center & Ringkasan Sistem"
      :subtitle="`Terhubung sebagai ${user?.name} (${user?.role})`"
    />

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 overflow-y-auto">
      
      <!-- Welcome Hero Banner -->
      <div class="glass-panel rounded-3xl p-6 sm:p-8 relative overflow-hidden">
        <div class="relative z-10 space-y-3 max-w-2xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>BFF Encrypted Session Active</span>
          </div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            Selamat Datang di BangLipai Control Center, {{ user?.name }}! ✨
          </h2>
          <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
            Anda masuk dengan otorisasi <span class="font-bold text-indigo-600 dark:text-sky-400">{{ user?.role }}</span>. Seluruh operasi hak akses terproteksi oleh Hypervel Coroutine Engine & BangLipai Secure Portal.
          </p>
        </div>
        
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-gradient-to-tr from-indigo-500/20 to-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
      </div>

      <!-- Metric Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="glass-panel rounded-2xl p-5 space-y-2 hover:border-emerald-500/50 transition-all">
          <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-mono font-medium">CLUSTER HEALTH</span>
            <Server class="w-4 h-4 text-emerald-500" />
          </div>
          <p class="text-2xl font-black text-slate-900 dark:text-white">100%</p>
          <div class="flex items-center gap-1.5 text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>8 Nodes Operational</span>
          </div>
        </div>

        <div class="glass-panel rounded-2xl p-5 space-y-2 hover:border-sky-500/50 transition-all">
          <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-mono font-medium">BFF THROUGHPUT</span>
            <Zap class="w-4 h-4 text-sky-500" />
          </div>
          <p class="text-2xl font-black text-slate-900 dark:text-white">14.2k <span class="text-xs font-normal text-slate-400">rps</span></p>
          <p class="text-[11px] text-slate-500 font-mono">Swoole Coroutine Pool</p>
        </div>

        <div class="glass-panel rounded-2xl p-5 space-y-2 hover:border-indigo-500/50 transition-all">
          <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-mono font-medium">ACTIVE DIRECTORY</span>
            <Users class="w-4 h-4 text-indigo-500" />
          </div>
          <p class="text-2xl font-black text-slate-900 dark:text-white">5 <span class="text-xs font-normal text-slate-400">users</span></p>
          <p class="text-[11px] text-slate-500 font-mono">Zero-Trust Directory</p>
        </div>

        <div class="glass-panel rounded-2xl p-5 space-y-2 hover:border-rose-500/50 transition-all">
          <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-mono font-medium">AI OPS TELEMETRY</span>
            <Bot class="w-4 h-4 text-rose-500" />
          </div>
          <p class="text-2xl font-black text-slate-900 dark:text-white">Karina</p>
          <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">Monitoring & Active</p>
        </div>

      </div>

      <!-- Role Capability Navigation Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        
        <div
          class="glass-panel rounded-2xl p-5 space-y-3 border-t-4 transition-all"
          :class="isSuperadmin ? 'border-t-rose-500 hover:shadow-lg hover:shadow-rose-500/5' : 'border-t-slate-300 dark:border-t-slate-700 opacity-80'"
        >
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Superadmin Level</h3>
            <Tag value="Full Root" severity="danger" class="!text-[10px]" />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Akses tanpa batas ke seluruh topologi Proxmox, private subnets (10.10.10.x), dan audit log sistem secara lengkap.
          </p>
          <NuxtLink
            v-if="isSuperadmin"
            to="/dashboard/cluster"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:gap-2 transition-all cursor-pointer"
          >
            <span>Buka Klaster & Subnet</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </NuxtLink>
        </div>

        <div
          class="glass-panel rounded-2xl p-5 space-y-3 border-t-4 transition-all"
          :class="canManageUsers ? 'border-t-sky-500 hover:shadow-lg hover:shadow-sky-500/5' : 'border-t-slate-300 dark:border-t-slate-700 opacity-80'"
        >
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Admin Account Level</h3>
            <Tag value="User Management" severity="info" class="!text-[10px]" />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Pengelolaan akun, penugasan role pengguna, reset kredensial sandi, dan pemantauan aktivitas user ekosistem.
          </p>
          <NuxtLink
            v-if="canManageUsers"
            to="/dashboard/users"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-sky-600 dark:text-sky-400 hover:gap-2 transition-all cursor-pointer"
          >
            <span>Buka Manajemen User</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </NuxtLink>
        </div>

        <div
          class="glass-panel rounded-2xl p-5 space-y-3 border-t-4 border-t-indigo-500 hover:shadow-lg hover:shadow-indigo-500/5 transition-all"
        >
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">User Self-Service</h3>
            <Tag value="Self Control" severity="secondary" class="!text-[10px]" />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Pengaturan profil mandiri, registrasi kunci passkey WebAuthn, otentikasi dua faktor (MFA), dan sesi aktif.
          </p>
          <NuxtLink
            to="/dashboard/profile"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:gap-2 transition-all cursor-pointer"
          >
            <span>Buka Pengaturan Profil</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </NuxtLink>
        </div>

      </div>

    </main>
  </div>
</template>
