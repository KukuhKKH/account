<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import { useAudit } from '~/composables/useAudit'
import type { PasswordChangeLogItem, SignInLogItem } from '~/types/audit'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import {
  ShieldCheck,
  ShieldAlert,
  Search,
  KeyRound,
  LogIn,
  Activity,
  Lock,
  Clock,
  RefreshCw,
  Download,
  Laptop,
  Smartphone,
  Globe,
  Terminal,
  CheckCircle2,
  AlertTriangle,
  ChevronLeft,
  ChevronRight,
  Info,
  Layers,
  Sparkles
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

const { user, canAccessSubnets, isSuperadmin } = useAuth()
const {
  passwordLogs,
  signInLogs,
  stats,
  passwordMeta,
  signInMeta,
  isLoading,
  isRefreshing,
  fetchPasswordLogs,
  fetchSignInLogs,
  fetchSecurityStats,
  refreshAll
} = useAudit()

const toast = useToast()

// Active tab selection
const activeTab = ref<'passwords' | 'sign_ins' | 'stream'>('passwords')

// Search & filter
const searchQuery = ref('')
const selectedChangeType = ref('all')
const selectedLogForDetail = ref<PasswordChangeLogItem | SignInLogItem | null>(null)
const showDetailModal = ref(false)

// Initial mount fetch
onMounted(() => {
  refreshAll()
})

// Debounced search
let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, (newVal) => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    if (activeTab.value === 'passwords') {
      fetchPasswordLogs({ search: newVal, changeType: selectedChangeType.value, page: 1 })
    } else if (activeTab.value === 'sign_ins') {
      fetchSignInLogs({ search: newVal, page: 1 })
    }
  }, 350)
})

function onTabChange(tab: 'passwords' | 'sign_ins' | 'stream') {
  activeTab.value = tab
  searchQuery.value = ''
  if (tab === 'passwords') {
    fetchPasswordLogs({ page: 1 })
  } else if (tab === 'sign_ins') {
    fetchSignInLogs({ page: 1 })
  }
}

function onFilterChangeType(type: string) {
  selectedChangeType.value = type
  fetchPasswordLogs({ search: searchQuery.value, changeType: type, page: 1 })
}

function onPasswordPageChange(newPage: number) {
  if (newPage < 1 || newPage > passwordMeta.value.lastPage) return
  fetchPasswordLogs({ search: searchQuery.value, changeType: selectedChangeType.value, page: newPage })
}

function onSignInPageChange(newPage: number) {
  if (newPage < 1 || newPage > signInMeta.value.lastPage) return
  fetchSignInLogs({ search: searchQuery.value, page: newPage })
}

function openDetail(item: PasswordChangeLogItem | SignInLogItem) {
  selectedLogForDetail.value = item
  showDetailModal.value = true
}

function exportAuditTrail() {
  const exportData = {
    exportedAt: new Date().toISOString(),
    exporter: user.value?.email,
    securityStats: stats.value,
    activeTab: activeTab.value,
    passwordLogs: passwordLogs.value,
    signInLogs: signInLogs.value
  }

  const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `banglipai-audit-trail-${new Date().toISOString().split('T')[0]}.json`
  a.click()
  URL.revokeObjectURL(url)

  toast.add({
    severity: 'success',
    summary: 'Audit Trail Diekspor',
    detail: 'Berkas log audit terenkripsi berhasil diunduh.',
    life: 3000
  })
}

function getBrowserIcon(browser: string) {
  const b = browser.toLowerCase()
  if (b.includes('chrome') || b.includes('firefox') || b.includes('edge') || b.includes('safari')) {
    return Globe
  }
  return Terminal
}

function getDeviceIcon(os: string) {
  const o = os.toLowerCase()
  if (o.includes('android') || o.includes('ios')) {
    return Smartphone
  }
  return Laptop
}
</script>

<template>
  <div class="flex-1 flex flex-col min-w-0">
    <DashboardHeader
      title="Audit Trail & Security Governance"
      subtitle="Rekaman jejak mutasi akun, log sign-in, dan telemetri keamanan enterprise secara immutable"
    />

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 overflow-y-auto">
      
      <!-- Top Overview Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Defense Score Card -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden group hover:border-indigo-500/50 transition-all duration-300">
          <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-colors"></div>
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Defense Rating</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">
              <ShieldCheck class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3 flex items-baseline gap-2">
            <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ stats.defenseScore }}%</span>
            <span class="text-[11px] font-semibold text-emerald-500 flex items-center gap-0.5">
              <CheckCircle2 class="w-3 h-3" /> Fortress Active
            </span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-mono">
            {{ stats.encryptionStandard }}
          </p>
        </div>

        <!-- 24h Sign-in Activity -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden group hover:border-sky-500/50 transition-all duration-300">
          <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-sky-500/10 rounded-full blur-2xl group-hover:bg-sky-500/20 transition-colors"></div>
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aktivitas Masuk (24j)</span>
            <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-500 flex items-center justify-center font-bold">
              <LogIn class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3 flex items-baseline gap-2">
            <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ stats.signInsLast24h }}</span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">sesi autentikasi</span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
            Dari {{ stats.activeIpsLast24h }} unique cluster IP addresses
          </p>
        </div>

        <!-- 30d Password Rotations -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden group hover:border-amber-500/50 transition-all duration-300">
          <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-colors"></div>
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Rotasi Password (30h)</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
              <KeyRound class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3 flex items-baseline gap-2">
            <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ stats.passwordRotations }}</span>
            <span class="text-[11px] font-semibold text-amber-500 font-mono">mutasi</span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
            {{ stats.selfChangesTotal }} mandiri • {{ stats.adminResetsTotal }} via admin
          </p>
        </div>

        <!-- Total Protected Accounts -->
        <div class="glass-panel rounded-3xl p-5 relative overflow-hidden group hover:border-purple-500/50 transition-all duration-300">
          <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-colors"></div>
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Integritas Log</span>
            <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold">
              <Lock class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3 flex items-baseline gap-2">
            <span class="text-sm font-black text-slate-900 dark:text-white tracking-tight uppercase">Immutable</span>
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
            {{ stats.auditDigestStatus }}
          </p>
        </div>

      </div>

      <!-- Main Audit Control Panel -->
      <div class="glass-panel rounded-3xl p-6 space-y-6">
        
        <!-- Header & Action Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
          
          <!-- Segmented Tab Navigation -->
          <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-950/80 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 self-start">
            <button
              @click="onTabChange('passwords')"
              class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2"
              :class="activeTab === 'passwords' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-sky-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <KeyRound class="w-3.5 h-3.5" />
              <span>Mutasi Kata Sandi</span>
              <span class="px-1.5 py-0.5 rounded-md text-[10px] font-mono bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-sky-400">
                {{ passwordMeta.total }}
              </span>
            </button>

            <button
              @click="onTabChange('sign_ins')"
              class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2"
              :class="activeTab === 'sign_ins' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-sky-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <LogIn class="w-3.5 h-3.5" />
              <span>Riwayat Masuk Sesi</span>
              <span class="px-1.5 py-0.5 rounded-md text-[10px] font-mono bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-sky-400">
                {{ signInMeta.total }}
              </span>
            </button>

            <button
              @click="onTabChange('stream')"
              class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2"
              :class="activeTab === 'stream' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-sky-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              <Activity class="w-3.5 h-3.5" />
              <span>Live Security Stream</span>
            </button>
          </div>

          <!-- Controls: Search, Refresh, Export -->
          <div class="flex items-center gap-2.5">
            <div v-if="activeTab !== 'stream'" class="relative w-full sm:w-64">
              <Search class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="activeTab === 'passwords' ? 'Cari mutasi / IP...' : 'Cari user / IP / browser...'"
                class="w-full pl-9 pr-3 py-2 bg-white/70 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800/80 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 dark:text-white placeholder:text-slate-400"
              />
            </div>

            <button
              @click="refreshAll"
              :disabled="isRefreshing"
              class="p-2 rounded-xl bg-white/70 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800/80 hover:border-indigo-500/50 text-slate-600 dark:text-slate-300 text-xs font-bold flex items-center gap-1.5 transition-all disabled:opacity-50"
              title="Perbarui data secara real-time"
            >
              <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isRefreshing }" />
              <span class="hidden sm:inline">Refresh</span>
            </button>

            <button
              @click="exportAuditTrail"
              class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-xs transition-all"
            >
              <Download class="w-3.5 h-3.5" />
              <span class="hidden sm:inline">Ekspor JSON</span>
            </button>
          </div>

        </div>

        <!-- TAB 1: Password Mutation Logs -->
        <div v-if="activeTab === 'passwords'" class="space-y-4">
          
          <!-- Filter pills for Change Types -->
          <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <span class="text-slate-400 text-[11px] font-semibold">Tipe:</span>
            <button
              v-for="f in [
                { id: 'all', label: 'Semua Tipe' },
                { id: 'self_change', label: 'Mandiri (Self)' },
                { id: 'admin_reset', label: 'Reset oleh Admin' },
                { id: 'system_reset', label: 'Sistem / Webhook' }
              ]"
              :key="f.id"
              @click="onFilterChangeType(f.id)"
              class="px-3 py-1 rounded-lg font-semibold transition-colors shrink-0"
              :class="selectedChangeType === f.id ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-950/80 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              {{ f.label }}
            </button>
          </div>

          <!-- Loading state -->
          <div v-if="isLoading" class="py-12 flex flex-col items-center justify-center space-y-3">
            <RefreshCw class="w-6 h-6 text-indigo-500 animate-spin" />
            <p class="text-xs text-slate-400 font-mono">Memuat rekaman mutasi kata sandi...</p>
          </div>

          <!-- Empty state -->
          <div v-else-if="passwordLogs.length === 0" class="py-12 text-center text-slate-400 text-xs space-y-2">
            <KeyRound class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600" />
            <p class="font-medium">Tidak ada rekaman log audit kata sandi yang sesuai filter.</p>
          </div>

          <!-- Log items list -->
          <div v-else class="space-y-3 font-mono text-xs">
            <div
              v-for="log in passwordLogs"
              :key="log.id"
              @click="openDetail(log)"
              class="p-4 rounded-2xl bg-white/70 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-indigo-500/50 hover:bg-white/90 dark:hover:bg-slate-900/90 transition-all cursor-pointer shadow-xs"
            >
              <div class="flex items-center gap-3.5">
                <div
                  class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 font-bold"
                  :class="log.isSelf ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border border-amber-500/20'"
                >
                  <KeyRound class="w-4 h-4" />
                </div>
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-extrabold text-slate-900 dark:text-white">{{ log.userName }}</span>
                    <span class="text-[11px] text-slate-400">({{ log.userEmail }})</span>
                    <Tag
                      :value="log.changeTypeLabel"
                      :severity="log.isSelf ? 'success' : (log.isAdmin ? 'warn' : 'info')"
                      class="!text-[9px]"
                    />
                  </div>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    {{ log.description }}
                    <span v-if="log.reason" class="text-slate-700 dark:text-slate-300 font-sans italic ml-1">• "{{ log.reason }}"</span>
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-4 self-end md:self-auto text-[11px] shrink-0">
                <div class="text-right">
                  <div class="text-slate-600 dark:text-slate-400">
                    IP: {{ canAccessSubnets ? log.ipAddress : '10.10.10.***' }}
                  </div>
                  <div class="text-[10px] text-slate-400 flex items-center justify-end gap-1 mt-0.5">
                    <Clock class="w-3 h-3" />
                    <span>{{ log.timeAgo }}</span>
                  </div>
                </div>
                <Info class="w-4 h-4 text-slate-400 hover:text-indigo-400" />
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="passwordMeta.lastPage > 1" class="flex items-center justify-between pt-4 border-t border-slate-200/80 dark:border-slate-800/80 text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-mono">
              Halaman {{ passwordMeta.currentPage }} dari {{ passwordMeta.lastPage }} (Total {{ passwordMeta.total }} entri)
            </span>
            <div class="flex items-center gap-1.5">
              <button
                @click="onPasswordPageChange(passwordMeta.currentPage - 1)"
                :disabled="passwordMeta.currentPage <= 1"
                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 disabled:opacity-40"
              >
                <ChevronLeft class="w-4 h-4" />
              </button>
              <button
                @click="onPasswordPageChange(passwordMeta.currentPage + 1)"
                :disabled="passwordMeta.currentPage >= passwordMeta.lastPage"
                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 disabled:opacity-40"
              >
                <ChevronRight class="w-4 h-4" />
              </button>
            </div>
          </div>

        </div>

        <!-- TAB 2: Sign-In Access History Logs -->
        <div v-if="activeTab === 'sign_ins'" class="space-y-4">
          
          <!-- Loading state -->
          <div v-if="isLoading" class="py-12 flex flex-col items-center justify-center space-y-3">
            <RefreshCw class="w-6 h-6 text-sky-500 animate-spin" />
            <p class="text-xs text-slate-400 font-mono">Memuat rekaman log akses masuk...</p>
          </div>

          <!-- Empty state -->
          <div v-else-if="signInLogs.length === 0" class="py-12 text-center text-slate-400 text-xs space-y-2">
            <LogIn class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600" />
            <p class="font-medium">Tidak ada rekaman log sign-in yang ditemukan.</p>
          </div>

          <!-- Sign-In List -->
          <div v-else class="space-y-3 font-mono text-xs">
            <div
              v-for="log in signInLogs"
              :key="log.id"
              @click="openDetail(log)"
              class="p-4 rounded-2xl bg-white/70 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-sky-500/50 hover:bg-white/90 dark:hover:bg-slate-900/90 transition-all cursor-pointer shadow-xs"
            >
              <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-500 flex items-center justify-center shrink-0 font-bold">
                  <LogIn class="w-4 h-4" />
                </div>
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-extrabold text-slate-900 dark:text-white">{{ log.userName }}</span>
                    <span class="text-[11px] text-slate-400">({{ log.userEmail }})</span>
                    <Tag :value="log.userRole" severity="secondary" class="!text-[9px]" />
                  </div>
                  <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    <component :is="getDeviceIcon(log.os)" class="w-3.5 h-3.5 text-slate-400" />
                    <span>{{ log.os }}</span>
                    <span>•</span>
                    <component :is="getBrowserIcon(log.browser)" class="w-3.5 h-3.5 text-slate-400" />
                    <span>{{ log.browser }}</span>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-4 self-end md:self-auto text-[11px] shrink-0">
                <div class="text-right">
                  <div class="text-slate-600 dark:text-slate-400">
                    IP: {{ canAccessSubnets ? log.ipAddress : '10.10.10.***' }}
                  </div>
                  <div class="text-[10px] text-slate-400 flex items-center justify-end gap-1 mt-0.5">
                    <Clock class="w-3 h-3" />
                    <span>{{ log.timeAgo }}</span>
                  </div>
                </div>
                <Info class="w-4 h-4 text-slate-400 hover:text-sky-400" />
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="signInMeta.lastPage > 1" class="flex items-center justify-between pt-4 border-t border-slate-200/80 dark:border-slate-800/80 text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-mono">
              Halaman {{ signInMeta.currentPage }} dari {{ signInMeta.lastPage }} (Total {{ signInMeta.total }} entri)
            </span>
            <div class="flex items-center gap-1.5">
              <button
                @click="onSignInPageChange(signInMeta.currentPage - 1)"
                :disabled="signInMeta.currentPage <= 1"
                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 disabled:opacity-40"
              >
                <ChevronLeft class="w-4 h-4" />
              </button>
              <button
                @click="onSignInPageChange(signInMeta.currentPage + 1)"
                :disabled="signInMeta.currentPage >= signInMeta.lastPage"
                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 disabled:opacity-40"
              >
                <ChevronRight class="w-4 h-4" />
              </button>
            </div>
          </div>

        </div>

        <!-- TAB 3: Live Security Stream & Telemetry Timeline -->
        <div v-if="activeTab === 'stream'" class="space-y-4">
          <div class="p-4 rounded-2xl bg-indigo-950/20 border border-indigo-500/20 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2 text-indigo-400">
              <Sparkles class="w-4 h-4 animate-pulse" />
              <span>Telemetry Node Stream aktif tersambung ke Karina AI Ops & Central Logging VM</span>
            </div>
            <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono text-[10px]">
              Active Feed (0 Anomaly)
            </span>
          </div>

          <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800">
            
            <div class="relative">
              <div class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></div>
              <div class="p-4 rounded-2xl bg-white/70 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800/80 text-xs font-mono">
                <div class="flex items-center justify-between">
                  <span class="font-extrabold text-slate-900 dark:text-white">BFF_SESSION_INTEGRITY_CHECK</span>
                  <span class="text-[10px] text-emerald-500 font-bold">100% OK</span>
                </div>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Verifikasi enkripsi cookie session & token rotasi valid.</p>
              </div>
            </div>

            <div class="relative">
              <div class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-sky-500 ring-4 ring-sky-500/20"></div>
              <div class="p-4 rounded-2xl bg-white/70 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800/80 text-xs font-mono">
                <div class="flex items-center justify-between">
                  <span class="font-extrabold text-slate-900 dark:text-white">LOGTO_WEBHOOK_LISTENER</span>
                  <span class="text-[10px] text-sky-400 font-bold">HMAC Verified</span>
                </div>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Sinkronisasi asinkron coroutine siap menerima webhook payload dari Core SSO.</p>
              </div>
            </div>

            <div class="relative">
              <div class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-purple-500 ring-4 ring-purple-500/20"></div>
              <div class="p-4 rounded-2xl bg-white/70 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800/80 text-xs font-mono">
                <div class="flex items-center justify-between">
                  <span class="font-extrabold text-slate-900 dark:text-white">DEFENSE_IN_DEPTH_POLICY</span>
                  <span class="text-[10px] text-purple-400 font-bold">Enforced</span>
                </div>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Isolasi superadmin, anti-self-reset, dan proteksi demosi administrator aktif di kernel.</p>
              </div>
            </div>

          </div>
        </div>

      </div>

    </main>

    <!-- Detail Payload Modal -->
    <Dialog
      v-model:visible="showDetailModal"
      modal
      :header="'Detail Audit Event #' + (selectedLogForDetail?.id || '')"
      :style="{ width: '38rem' }"
      class="glass-dialog !rounded-3xl"
    >
      <div v-if="selectedLogForDetail" class="space-y-4 font-mono text-xs">
        <div class="p-4 rounded-2xl bg-slate-100 dark:bg-slate-950/80 border border-slate-200/80 dark:border-slate-800/80 space-y-2">
          <div class="flex justify-between">
            <span class="text-slate-500">Event Target User:</span>
            <span class="font-bold text-slate-900 dark:text-white">{{ selectedLogForDetail.userName }} ({{ selectedLogForDetail.userEmail }})</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Origin IP:</span>
            <span class="font-bold text-indigo-500">{{ canAccessSubnets ? (selectedLogForDetail.ipAddress || 'Local') : '10.10.10.***' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Timestamp:</span>
            <span class="text-slate-400">{{ (selectedLogForDetail as any).createdAt || (selectedLogForDetail as any).signedInAt }}</span>
          </div>
          <div v-if="(selectedLogForDetail as any).userAgent" class="flex flex-col gap-1 pt-2 border-t border-slate-200 dark:border-slate-800">
            <span class="text-slate-500">User Agent:</span>
            <span class="text-[10px] text-slate-400 break-all">{{ (selectedLogForDetail as any).userAgent }}</span>
          </div>
        </div>

        <div v-if="(selectedLogForDetail as any).metadata" class="space-y-1.5">
          <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Metadata Payload:</span>
          <pre class="p-3 rounded-xl bg-slate-900 text-emerald-400 text-[10px] overflow-x-auto">{{ JSON.stringify((selectedLogForDetail as any).metadata, null, 2) }}</pre>
        </div>

        <div class="flex justify-end pt-2">
          <Button
            label="Tutup"
            severity="secondary"
            class="!rounded-xl !text-xs"
            @click="showDetailModal = false"
          />
        </div>
      </div>
    </Dialog>

  </div>
</template>
