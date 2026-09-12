<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useClusterStore } from '~/stores/cluster'
import {
  Radio,
  Eye,
  EyeOff,
  Lock,
  ChevronRight,
  Server
} from 'lucide-vue-next'

const authStore = useAuthStore()
const clusterStore = useClusterStore()
</script>

<template>
  <section id="topology" class="scroll-mt-24 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
      <div>
        <div class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
          <Radio class="w-3.5 h-3.5" />
          Cluster Topology
        </div>
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight mt-1">
          Topologi Node & Klaster Layanan
        </h2>
      </div>
      
      <!-- Superadmin Protected Subnet Inspection Toggle -->
      <div class="flex items-center gap-3">
        <!-- ONLY VISIBLE WHEN LOGGED IN AND ROLE IS SUPERADMIN -->
        <div v-if="authStore.canAccessSubnets" class="flex items-center gap-2">
          <button
            @click="clusterStore.toggleSubnetDetails()"
            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer shadow-xs"
            :class="clusterStore.showDevDetails 
              ? 'bg-indigo-600 text-white border-indigo-600 shadow-indigo-600/25' 
              : 'bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800'"
          >
            <Eye v-if="!clusterStore.showDevDetails" class="w-3.5 h-3.5" />
            <EyeOff v-else class="w-3.5 h-3.5" />
            <span>{{ clusterStore.showDevDetails ? 'Sembunyikan Detail Subnet' : 'Tampilkan Detail Subnet' }}</span>
          </button>
          <Tag value="Superadmin" severity="danger" class="!text-[10px]" />
        </div>

        <!-- HINT FOR LOGGED IN NON-SUPERADMIN (e.g. Admin Account or User) -->
        <div v-else-if="authStore.isLoggedIn" class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-mono">
          <Lock class="w-3.5 h-3.5 text-amber-500 shrink-0" />
          <span>Subnet Terproteksi (Role: {{ authStore.currentUser?.role }})</span>
        </div>

        <!-- HINT FOR GUEST / PUBLIC -->
        <div v-else class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-mono">
          <Lock class="w-3.5 h-3.5 text-slate-400 shrink-0" />
          <span>Login Superadmin untuk Melihat Subnet Privat</span>
        </div>
      </div>
    </div>

    <!-- Nodes Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="node in clusterStore.nodes"
        :key="node.id"
        class="glass-panel glass-panel-hover rounded-2xl p-5 relative group cursor-pointer transition-all duration-200 hover:-translate-y-1"
        @click="clusterStore.selectNode(node)"
      >
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
              <component :is="node.icon || Server" class="w-4 h-4 text-sky-600 dark:text-sky-400" />
            </div>
            <div>
              <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ node.name }}</h4>
              <span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                {{ node.type }}
              </span>
            </div>
          </div>
          
          <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[11px] font-mono text-emerald-600 dark:text-emerald-400">{{ node.ping }}</span>
          </div>
        </div>

        <p class="text-xs text-slate-600 dark:text-slate-400 mb-4 line-clamp-2">
          {{ node.role }}
        </p>

        <!-- IP Address & Zone Footer -->
        <div class="border-t border-slate-200/80 dark:border-slate-800/80 pt-3 flex items-center justify-between text-xs font-mono">
          <div>
            <span class="text-[10px] text-slate-400 block">INTERNAL IP</span>
            <span class="font-semibold" :class="authStore.canAccessSubnets && clusterStore.showDevDetails ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-400 dark:text-slate-500'">
              {{ authStore.canAccessSubnets && clusterStore.showDevDetails ? node.internalIp : node.maskedIp }}
            </span>
          </div>
          
          <div class="text-right">
            <span class="text-[10px] text-slate-400 block">ZONE</span>
            <span class="text-slate-700 dark:text-slate-300 text-[11px] font-medium">{{ node.zone.split(' ')[0] }}</span>
          </div>
        </div>

        <!-- Hover detail hint -->
        <div class="absolute inset-x-0 bottom-0 py-1 bg-indigo-600/90 text-white text-[10px] text-center font-medium rounded-b-2xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1">
          <span>Inspeksi Detail Node</span>
          <ChevronRight class="w-3 h-3" />
        </div>
      </div>
    </div>
  </section>
</template>
