<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useClusterStore } from '~/stores/cluster'
import {
  ShieldCheck,
  Zap,
  Lock,
  ChevronRight,
  Terminal,
  Activity,
  Layers,
  Users
} from 'lucide-vue-next'

const authStore = useAuthStore()
const clusterStore = useClusterStore()
</script>

<template>
  <section class="relative pt-6 sm:pt-12 pb-16 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
        
        <!-- Left Hero Copy -->
        <div class="lg:col-span-7 space-y-6">
          
          <!-- Top Badge -->
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-indigo-200/80 dark:border-indigo-500/30 bg-indigo-50/80 dark:bg-indigo-950/40 text-xs font-semibold text-indigo-700 dark:text-indigo-300 shadow-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Hypervel Swoole Coroutine Engine</span>
            <span class="text-slate-300 dark:text-slate-700">|</span>
            <span class="font-mono text-emerald-600 dark:text-emerald-400">14k+ RPS @ 1.8ms</span>
          </div>

          <!-- Main Heading -->
          <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.12]">
            SSO & Identitas Terpusat <br />
            <span class="bg-gradient-to-r from-indigo-600 via-sky-500 to-emerald-400 bg-clip-text text-transparent">
              Cepat, Andal & Aman.
            </span>
          </h1>

          <!-- Subtitle / Tagline -->
          <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-xl leading-relaxed">
            Pondasi Single Sign-On (SSO) dan tata kelola hak akses berbasis coroutine memory-resident untuk seluruh ekosistem <span class="font-semibold text-slate-900 dark:text-white">banglipai.web.id</span> dengan proteksi identitas zero-trust.
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-wrap items-center gap-3 pt-2">
            <NuxtLink
              v-if="authStore.isLoggedIn"
              to="/dashboard"
              class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/40 hover:-translate-y-0.5 transition-all cursor-pointer"
            >
              <span>Buka Control Dashboard</span>
              <ChevronRight class="w-4 h-4" />
            </NuxtLink>

            <button
              v-else
              @click="authStore.openSsoModal()"
              class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/40 hover:-translate-y-0.5 transition-all cursor-pointer"
            >
              <Users class="w-4 h-4" />
              <span>Masuk BangLipai Secure Portal</span>
              <ChevronRight class="w-4 h-4" />
            </button>

            <a
              href="#topology"
              class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 hover:bg-slate-100 dark:hover:bg-slate-800/80 text-slate-700 dark:text-slate-200 font-semibold text-sm backdrop-blur-md transition-all cursor-pointer"
            >
              <Layers class="w-4 h-4 text-sky-500" />
              <span>Jelajahi Topologi</span>
            </a>
          </div>

          <!-- Feature Highlights -->
          <div class="grid grid-cols-3 gap-3 pt-4 border-t border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div>
              <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base font-mono">1.8 ms</p>
              <p class="text-slate-500 dark:text-slate-400">P99 Latency</p>
            </div>
            <div>
              <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base font-mono">Zero-Trust</p>
              <p class="text-slate-500 dark:text-slate-400">Subnet Masking</p>
            </div>
            <div>
              <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base font-mono">BFF Ready</p>
              <p class="text-slate-500 dark:text-slate-400">Encrypted Session</p>
            </div>
          </div>

        </div>

        <!-- Right Interactive Terminal Widget -->
        <div class="lg:col-span-5">
          <div class="relative">
            <!-- Glow background -->
            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-sky-500 to-emerald-500 rounded-3xl blur-xl opacity-30 dark:opacity-40 animate-tilt"></div>
            
            <div class="relative glass-panel rounded-3xl p-5 shadow-2xl border border-white/20 dark:border-slate-700/60">
              
              <!-- Window Header -->
              <div class="flex items-center justify-between pb-3 border-b border-slate-200/60 dark:border-slate-800/60">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full bg-rose-500/80"></div>
                  <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                  <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                  <span class="text-xs font-mono text-slate-500 dark:text-slate-400 ml-2">hypervel-coroutine.sh</span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] font-mono text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md">
                  <Activity class="w-3 h-3 animate-spin" />
                  <span>LIVE COROUTINES</span>
                </div>
              </div>

              <!-- Terminal Code Block -->
              <div class="pt-3 font-mono text-xs space-y-2 text-slate-700 dark:text-slate-300">
                <div class="flex items-center gap-2 text-slate-400">
                  <Terminal class="w-3.5 h-3.5 text-indigo-500" />
                  <span>$ identity-engine --status</span>
                </div>
                
                <div class="p-3 rounded-xl bg-slate-900 text-slate-100 space-y-1.5 shadow-inner">
                  <div class="flex justify-between text-[11px]">
                    <span class="text-indigo-400">» Swoole Coroutine Pool:</span>
                    <span class="text-emerald-400 font-bold">512 Active Workers</span>
                  </div>
                  <div class="flex justify-between text-[11px]">
                    <span class="text-sky-400">» Memory Resident Cache:</span>
                    <span class="text-sky-300">Redis Central Vault @ 10.10.10.***</span>
                  </div>
                  <div class="flex justify-between text-[11px]">
                    <span class="text-amber-400">» Authentication Protocol:</span>
                    <span class="text-amber-300">BFF Session + WebAuthn</span>
                  </div>
                  <div class="flex justify-between text-[11px]">
                    <span class="text-purple-400">» AI Telemetry Agent:</span>
                    <span class="text-purple-300">Karina (Online)</span>
                  </div>
                </div>

                <!-- Interactive Ping Tester in Terminal Card -->
                <div class="pt-2">
                  <div class="flex items-center justify-between p-3 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50">
                    <div>
                      <p class="text-[11px] font-bold text-slate-900 dark:text-white">Live Benchmark Probe</p>
                      <p class="text-[10px] text-slate-500 dark:text-slate-400">Test actual response time to edge node</p>
                    </div>
                    <button
                      @click="clusterStore.measureLivePing()"
                      :disabled="clusterStore.isPinging"
                      class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-semibold flex items-center gap-1.5 cursor-pointer shadow-xs transition-colors"
                    >
                      <Zap class="w-3 h-3" :class="{ 'animate-spin': clusterStore.isPinging }" />
                      <span>{{ clusterStore.isPinging ? 'Pinging...' : 'Ping Probe' }}</span>
                    </button>
                  </div>
                </div>

              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>
