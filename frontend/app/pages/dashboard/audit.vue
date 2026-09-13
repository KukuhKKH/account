<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import {
  ShieldCheck,
  Search,
  Filter,
  Lock,
  CheckCircle2,
  Clock
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

const { user, canAccessSubnets } = useAuth()

const auditLogs = ref([
  { id: 1, action: 'BFF_SESSION_LOGIN', actor: 'Kukuh', role: 'Superadmin', ip: '10.10.10.5', time: '10:42:15', status: 'Success' },
  { id: 2, action: 'NODE_HEALTH_PROBE', actor: 'Karina AI', role: 'System', ip: '10.10.10.99', time: '10:40:00', status: 'Optimal' },
  { id: 3, action: 'USER_ROLE_VERIFY', actor: 'DevOps Admin', role: 'Admin Account', ip: '10.10.10.40', time: '10:35:12', status: 'Success' },
  { id: 4, action: 'SUBNET_SECRET_QUERY', actor: 'Kukuh', role: 'Superadmin', ip: '10.10.10.5', time: '10:28:44', status: 'Authorized' },
  { id: 5, action: 'PASSWORD_ENTROPY_CHECK', actor: 'Ahmad Fauzi', role: 'User', ip: '10.10.10.***', time: '09:15:03', status: 'Passed' }
])
</script>

<template>
  <div class="flex-1 flex flex-col min-w-0">
    <DashboardHeader
      title="Audit Trail Keamanan & Log Aktivitas"
      subtitle="Rekaman jejak aktivitas login, verifikasi SSO, dan mutasi data secara immutable"
    />

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 overflow-y-auto">
      
      <div class="glass-panel rounded-3xl p-6 space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">
                Log Keamanan Terenkripsi
              </h2>
              <Tag value="Immutable Log" severity="secondary" class="!text-[10px]" />
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Event logging stream terhubung ke Central Logging DB
            </p>
          </div>

          <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
            <Lock class="w-3.5 h-3.5 text-emerald-500" />
            <span>Audit Digest Verified</span>
          </div>
        </div>

        <!-- Audit Items List -->
        <div class="space-y-3 font-mono text-xs">
          <div
            v-for="log in auditLogs"
            :key="log.id"
            class="p-4 rounded-2xl bg-white/70 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-indigo-500/50 transition-colors"
          >
            <div class="flex items-center gap-3.5">
              <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 flex items-center justify-center text-indigo-600 dark:text-sky-400 font-bold shrink-0 shadow-xs">
                <ShieldCheck class="w-4 h-4" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-extrabold text-slate-900 dark:text-white">{{ log.action }}</span>
                  <Tag
                    :value="log.role"
                    :severity="log.role === 'Superadmin' ? 'danger' : (log.role === 'System' ? 'info' : 'secondary')"
                    class="!text-[9px]"
                  />
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                  Pelaksana: <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ log.actor }}</span> • Origin IP: {{ canAccessSubnets ? log.ip : '10.10.10.***' }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-auto text-[11px]">
              <span class="text-emerald-600 dark:text-emerald-400 font-bold px-2 py-0.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                {{ log.status }}
              </span>
              <span class="text-slate-400 flex items-center gap-1">
                <Clock class="w-3 h-3" />
                <span>{{ log.time }}</span>
              </span>
            </div>
          </div>
        </div>

      </div>

    </main>
  </div>
</template>
