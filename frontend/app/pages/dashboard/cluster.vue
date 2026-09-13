<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import { useClusterStore } from '~/stores/cluster'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import {
  Server,
  Eye,
  EyeOff,
  ShieldCheck,
  Activity,
  Cpu,
  HardDrive,
  RefreshCw
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth', 'superadmin'],
  layout: 'dashboard'
})

const { user } = useAuth()
const clusterStore = useClusterStore()
</script>

<template>
  <div class="flex-1 flex flex-col min-w-0">
    <DashboardHeader
      title="Klaster Node & Subnet Terproteksi"
      subtitle="Topologi infrastruktur home-lab Proxmox, LXC container, dan VM di gateway 10.10.10.5"
    />

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 overflow-y-auto">
      
      <div class="glass-panel rounded-3xl p-6 space-y-6">
        
        <!-- Header Bar with Subnet Mask Toggle -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-200/80 dark:border-slate-800/80">
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">
                Node Klaster BangLipai
              </h2>
              <Tag value="Superadmin Privilege" severity="danger" class="!text-[10px]" />
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              8 Node Aktif • High-Availability Gateway Swarm
            </p>
          </div>

          <!-- Subnet Toggle Button -->
          <button
            type="button"
            @click="clusterStore.toggleSubnetDetails()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-semibold cursor-pointer transition-all shadow-xs"
            :class="clusterStore.showDevDetails 
              ? 'bg-indigo-600 text-white border border-indigo-600 shadow-md shadow-indigo-600/20' 
              : 'bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-300'"
          >
            <Eye v-if="!clusterStore.showDevDetails" class="w-4 h-4" />
            <EyeOff v-else class="w-4 h-4" />
            <span>{{ clusterStore.showDevDetails ? 'Sembunyikan IP Subnet' : 'Buka Detail IP Subnet' }}</span>
          </button>
        </div>

        <!-- Node Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="node in clusterStore.nodes"
            :key="node.id"
            class="p-5 rounded-3xl bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 space-y-4 cursor-pointer hover:border-indigo-500/80 hover:shadow-xl hover:shadow-indigo-500/5 transition-all group hover:-translate-y-0.5"
            @click="clusterStore.selectNode(node)"
          >
            <div class="flex items-center justify-between">
              <span
                class="text-[10px] font-mono uppercase px-2 py-0.5 rounded-lg font-bold"
                :class="node.type === 'VM' ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20' : 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20'"
              >
                {{ node.type }}
              </span>
              
              <div class="flex items-center gap-1.5 text-[11px] font-mono text-emerald-500">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>{{ node.ping }}</span>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-extrabold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-sky-400 transition-colors">
                {{ node.name }}
              </h3>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                {{ node.role }}
              </p>
            </div>

            <!-- IP Box -->
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/60 font-mono text-xs flex justify-between items-center">
              <span class="text-[10px] text-slate-400">IP ADDRESS:</span>
              <span class="font-bold text-xs" :class="clusterStore.showDevDetails ? 'text-indigo-600 dark:text-sky-400' : 'text-slate-400'">
                {{ clusterStore.showDevDetails ? node.internalIp : node.maskedIp }}
              </span>
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800/50">
              <span>Status: <span class="text-emerald-500 font-semibold font-mono">{{ node.status }}</span></span>
              <span class="text-indigo-500 font-semibold group-hover:translate-x-1 transition-transform">Detail →</span>
            </div>
          </div>
        </div>

      </div>

    </main>
  </div>
</template>
