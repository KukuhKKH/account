<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import {
  Bot,
  Terminal,
  Activity,
  Zap,
  Sparkles,
  ShieldCheck,
  Cpu,
  RefreshCw
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth', 'superadmin'],
  layout: 'dashboard'
})

const { user } = useAuth()
</script>

<template>
  <div class="flex-1 flex flex-col min-w-0">
    <DashboardHeader
      title="Karina AI Ops Autonomous Intelligence Center"
      subtitle="Pusat pemantauan kecerdasan buatan, telemetri coroutine, dan optimasi klaster home-lab"
    />

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 overflow-y-auto">
      
      <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">
        
        <!-- Karina Hero Profile Card -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200/80 dark:border-slate-800/80">
          <div class="flex items-center gap-4">
            <div class="relative">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-rose-500 via-purple-500 to-indigo-500 p-0.5 shadow-xl shadow-rose-500/20">
                <div class="w-full h-full bg-white dark:bg-slate-900 rounded-[14px] flex items-center justify-center text-rose-500">
                  <Bot class="w-7 h-7" />
                </div>
              </div>
              <span class="absolute -bottom-1 -right-1 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 border-2 border-white dark:border-slate-950"></span>
              </span>
            </div>

            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                  Karina AI Ops Agent
                </h2>
                <Tag value="Top-Level Certified DevOps & AI Engineer" severity="danger" class="!text-[10px]" />
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                VM Node: 10.10.10.99 (Zone-AI) • Memory Resident Coroutine Worker
              </p>
            </div>
          </div>

          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Autonomous Intelligence Active</span>
          </div>
        </div>

        <!-- Telemetry Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 space-y-1">
            <span class="text-[10px] font-mono text-slate-400">COROUTINE POOL FOOTPRINT</span>
            <p class="text-xl font-extrabold text-slate-900 dark:text-white">68.4 MB</p>
            <p class="text-[10px] text-emerald-500 font-mono">Zero Leak • Pooled</p>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 space-y-1">
            <span class="text-[10px] font-mono text-slate-400">AVERAGE LATENCY CLUSTER</span>
            <p class="text-xl font-extrabold text-slate-900 dark:text-white">&lt; 1.4 ms</p>
            <p class="text-[10px] text-sky-500 font-mono">Subnet 10.10.10.0/24</p>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 space-y-1">
            <span class="text-[10px] font-mono text-slate-400">SECURITY POSTURE</span>
            <p class="text-xl font-extrabold text-slate-900 dark:text-white">100%</p>
            <p class="text-[10px] text-emerald-500 font-mono">Zero Threats Detected</p>
          </div>
        </div>

        <!-- Live Intelligence Stream Terminal -->
        <div class="space-y-2">
          <div class="flex items-center justify-between text-xs text-slate-400 font-mono">
            <div class="flex items-center gap-2">
              <Terminal class="w-4 h-4 text-indigo-500" />
              <span>LIVE AI OPS TELEMETRY LOG</span>
            </div>
            <span>Auto-refresh: 1s</span>
          </div>

          <div class="p-5 rounded-3xl bg-slate-950 text-slate-100 font-mono text-xs space-y-2 border border-slate-800 shadow-2xl overflow-x-auto">
            <div class="text-rose-400 font-bold flex items-center gap-2">
              <Sparkles class="w-3.5 h-3.5" />
              <span>[Karina Autonomous System Audit]:</span>
            </div>
            <p class="text-slate-300">» [10.10.10.53] Local DNS queries resolved at 0.3ms (Cache Hit Ratio: 99.4%).</p>
            <p class="text-slate-300">» [10.10.10.60] Central PostgreSQL pool active connections: 12/100 (Optimal).</p>
            <p class="text-slate-300">» [10.10.10.40] DevOps Node runner is idle and ready for deployment jobs.</p>
            <p class="text-slate-300">» [10.10.10.10] Swarm Manager heartbeat synchronized with Worker 1 & Worker 2.</p>
            <p class="text-emerald-400 font-semibold">✔ Status: Ekosistem banglipai.web.id dalam kondisi prima dan aman untuk Mas Kukuh! ❤️</p>
          </div>
        </div>

      </div>

    </main>
  </div>
</template>
