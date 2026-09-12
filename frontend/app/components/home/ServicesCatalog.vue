<script setup lang="ts">
import { useClusterStore } from '~/stores/cluster'
import {
  Layers,
  ExternalLink,
  ShieldCheck,
  Zap
} from 'lucide-vue-next'

const clusterStore = useClusterStore()
</script>

<template>
  <section id="services" class="scroll-mt-24 space-y-6">
    <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
      <div class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
        <Layers class="w-3.5 h-3.5" />
        Ecosystem Services
      </div>
      <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight mt-1">
        Katalog Layanan & Microservices Terintegrasi
      </h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-2xl">
        Seluruh endpoint aplikasi di bawah domain banglipai.web.id terproteksi di bawah autentikasi Single Sign-On terpusat.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="service in clusterStore.services"
        :key="service.id"
        class="glass-panel glass-panel-hover rounded-2xl p-5 flex flex-col justify-between relative group"
      >
        <div>
          <!-- Header service -->
          <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-mono font-semibold uppercase px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/80">
              {{ service.badge }}
            </span>
            <div class="flex items-center gap-1.5 text-xs font-mono text-emerald-600 dark:text-emerald-400">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span>{{ service.status }}</span>
            </div>
          </div>

          <!-- Service Title -->
          <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-sky-400 transition-colors">
            {{ service.name }}
          </h3>
          
          <p class="text-xs font-mono text-slate-500 dark:text-slate-400 mb-3 truncate flex items-center gap-1.5">
            <span v-if="!service.isPublic" class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
            <span>{{ service.identifier }}</span>
          </p>

          <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
            {{ service.desc }}
          </p>
        </div>

        <!-- Footer Service Card -->
        <div class="border-t border-slate-200/80 dark:border-slate-800/80 pt-3 flex items-center justify-between">
          <div class="flex items-center gap-1 text-[11px] font-mono text-slate-500 dark:text-slate-400">
            <Zap class="w-3 h-3 text-amber-500" />
            <span>Avg Latency: {{ service.latency }}</span>
          </div>

          <a
            v-if="service.isPublic && service.url"
            :href="service.url"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-sky-400 hover:underline cursor-pointer"
          >
            <span>Buka Layanan</span>
            <ExternalLink class="w-3 h-3" />
          </a>

          <div
            v-else
            class="inline-flex items-center gap-1 text-[11px] font-mono font-medium text-slate-400 dark:text-slate-500"
          >
            <ShieldCheck class="w-3.5 h-3.5 text-emerald-500/80" />
            <span>Internal Mesh</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
