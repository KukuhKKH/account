<script setup lang="ts">
import { ref } from 'vue'

const title = ref('BangLipai Identity Management')
const isOnline = ref(true)
const count = ref(0)
</script>

<template>
  <main class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-6">
    <div class="max-w-xl w-full bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8 backdrop-blur-sm">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-sky-400 flex items-center justify-center font-bold text-lg text-white shadow-lg shadow-indigo-500/30">
            BL
          </div>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-white">{{ title }}</h1>
            <p class="text-xs text-slate-400">Nuxt 4 + PrimeVue v5 + Tailwind CSS v4</p>
          </div>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          Ready
        </span>
      </div>

      <div class="space-y-4 my-6">
        <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-700/50 flex items-center justify-between">
          <div>
            <div class="text-sm font-semibold text-slate-200">Backend Engine</div>
            <div class="text-xs text-slate-400">Hypervel (PHP 8.3 Coroutine / Swoole)</div>
          </div>
          <span class="text-xs font-mono px-2.5 py-1 rounded bg-slate-700 text-sky-300">Port 9501</span>
        </div>

        <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-700/50 flex items-center justify-between">
          <div>
            <div class="text-sm font-semibold text-slate-200">Frontend Engine</div>
            <div class="text-xs text-slate-400">Nuxt 4.x + PrimeVue v5 (Aura Theme)</div>
          </div>
          <span class="text-xs font-mono px-2.5 py-1 rounded bg-slate-700 text-indigo-300">Port 3000</span>
        </div>
      </div>

      <div class="flex items-center gap-3 pt-2">
        <Button 
          label="Test Counter" 
          icon="pi pi-bolt" 
          severity="info" 
          @click="count++" 
          class="flex-1"
        />
        <div class="px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 font-mono text-sm">
          Clicks: <span class="text-indigo-400 font-bold">{{ count }}</span>
        </div>
      </div>
    </div>
  </main>
</template>
