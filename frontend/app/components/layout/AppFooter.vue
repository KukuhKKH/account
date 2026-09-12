<script setup lang="ts">
import { ShieldCheck, Lock, Heart } from 'lucide-vue-next'
</script>

<template>
  <footer class="border-t border-slate-200/80 dark:border-slate-800/80 bg-white/50 dark:bg-slate-950/60 backdrop-blur-md py-12 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
        
        <div class="md:col-span-2 space-y-3">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
              <ShieldCheck class="w-4 h-4" />
            </div>
            <span class="font-bold text-base text-slate-900 dark:text-white">BangLipai Identity & SSO Engine</span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md leading-relaxed">
            Sistem Autentikasi dan Manajemen Identitas Terpusat generasi baru berbasis Hypervel Coroutine Engine & BangLipai Secure Portal. Dirancang untuk performa tinggi, isolasi RBAC ketat, dan perlindungan infrastruktur zero-trust.
          </p>
          <div class="flex items-center gap-2 text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            <span>All Cluster Nodes & Services Operational</span>
          </div>
        </div>

        <div class="space-y-2">
          <h5 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Arsitektur</h5>
          <ul class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
            <li>Hypervel Coroutine Engine</li>
            <li>Nuxt 4 + PrimeVue Aura</li>
            <li>BFF Session Protocol</li>
            <li>Central Database Vault</li>
            <li>Docker Swarm Mesh</li>
          </ul>
        </div>

        <div class="space-y-2">
          <h5 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Keamanan & RBAC</h5>
          <ul class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
            <li>Zero Trust Subnet Masking</li>
            <li>Superadmin Secret Vault</li>
            <li>Admin Account Governance</li>
            <li>MFA & WebAuthn Ready</li>
            <li>AI Ops Telemetry (Karina)</li>
          </ul>
        </div>

      </div>

      <div class="border-t border-slate-200 dark:border-slate-800/80 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-1.5">
          <span>&copy; {{ new Date().getFullYear() }} BangLipai Ecosystem. Dibuat dengan</span>
          <Heart class="w-3.5 h-3.5 text-rose-500 inline fill-rose-500" />
          <span>oleh Karina.</span>
        </div>
        <div class="flex items-center gap-4 text-[11px] font-mono">
          <span class="inline-flex items-center gap-1">
            <Lock class="w-3 h-3 text-emerald-500" />
            <span>Encrypted BFF Session</span>
          </span>
          <span>Node IP Sanitized</span>
        </div>
      </div>
    </div>
  </footer>
</template>
