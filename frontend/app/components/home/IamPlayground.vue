<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useClusterStore } from '~/stores/cluster'
import {
  KeyRound,
  ShieldCheck,
  Zap,
  CheckCircle2,
  XCircle,
  AlertTriangle,
  Lock,
  Cpu
} from 'lucide-vue-next'

const authStore = useAuthStore()
const clusterStore = useClusterStore()

const testPassword = ref('')
</script>

<template>
  <section id="iam" class="scroll-mt-24 space-y-8">
    <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
      <div class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
        <KeyRound class="w-3.5 h-3.5" />
        IAM & Coroutine Benchmarks
      </div>
      <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight mt-1">
        Validasi Keamanan Identitas & Analisis Benchmark
      </h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-2xl">
        Uji coba engine validasi sandi, matriks pembagian hak akses (RBAC), serta keunggulan coroutine Swoole dibanding arsitektur lawas.
      </p>
    </div>

    <!-- 2 Column: Password Validator & RBAC Matrix -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      <!-- Password Strength Tester -->
      <div class="lg:col-span-5 glass-panel rounded-3xl p-6 space-y-4">
        <div class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
          <Lock class="w-4 h-4 text-indigo-600 dark:text-sky-400" />
          <span>Interactive Password Strength Engine</span>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
          Sistem BangLipai Identity memberlakukan standar entropy tinggi untuk proteksi akun dari serangan credential stuffing.
        </p>

        <div class="space-y-3 pt-2">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
            Simulasi Input Sandi Baru:
          </label>
          <Password
            v-model="testPassword"
            placeholder="Ketik password untuk cek kekuatan..."
            class="w-full"
            inputClass="w-full !px-3.5 !py-2.5 !rounded-xl !text-xs !bg-white/80 dark:!bg-slate-900/80 !border-slate-300 dark:!border-slate-700 font-mono"
            toggleMask
            promptLabel="Masukkan kombinasi sandi kuat"
            weakLabel="Terlalu Lemah"
            mediumLabel="Kekuatan Sedang"
            strongLabel="Sangat Kuat & Aman"
          >
            <template #header>
              <div class="text-xs font-bold text-slate-700 dark:text-slate-300 pb-2">Standar Kompleksitas Sandi:</div>
            </template>
            <template #footer>
              <ul class="text-[11px] text-slate-600 dark:text-slate-400 space-y-1 pt-2 border-t border-slate-200 dark:border-slate-700">
                <li class="flex items-center gap-1.5">
                  <span :class="testPassword.length >= 8 ? 'text-emerald-500 font-bold' : 'text-slate-400'">• Minimal 8 karakter</span>
                </li>
                <li class="flex items-center gap-1.5">
                  <span :class="/[A-Z]/.test(testPassword) ? 'text-emerald-500 font-bold' : 'text-slate-400'">• Huruf besar & kecil</span>
                </li>
                <li class="flex items-center gap-1.5">
                  <span :class="/[0-9]/.test(testPassword) ? 'text-emerald-500 font-bold' : 'text-slate-400'">• Kombinasi angka numerik</span>
                </li>
                <li class="flex items-center gap-1.5">
                  <span :class="/[^A-Za-z0-9]/.test(testPassword) ? 'text-emerald-500 font-bold' : 'text-slate-400'">• Simbol khusus (!@#$%^&*)</span>
                </li>
              </ul>
            </template>
          </Password>
        </div>

        <div class="p-3 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 text-xs space-y-1">
          <p class="font-semibold text-indigo-900 dark:text-indigo-300">BFF Session Encryption</p>
          <p class="text-slate-500 dark:text-slate-400 text-[11px]">
            Setiap autentikasi diproses secara decoupled dengan cookie session aman dan isolasi memori coroutine.
          </p>
        </div>
      </div>

      <!-- RBAC Capability Matrix -->
      <div class="lg:col-span-7 glass-panel rounded-3xl p-6 space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
            <ShieldCheck class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
            <span>Matriks Hak Akses Terisolasi (RBAC)</span>
          </div>
          <Tag value="Granular Policy" severity="success" class="!text-[10px]" />
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
          Tiga tingkat hak akses dengan batas keamanan ketat di lingkungan banglipai.web.id:
        </p>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse font-mono">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 text-[11px]">
                <th class="pb-2.5 font-semibold">Fitur / Hak Akses</th>
                <th class="pb-2.5 font-semibold text-center text-rose-500">Superadmin</th>
                <th class="pb-2.5 font-semibold text-center text-sky-500">Admin Account</th>
                <th class="pb-2.5 font-semibold text-center text-slate-400">User</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
              <tr>
                <td class="py-2.5">Subnet & IP Privat Home-Lab</td>
                <td class="py-2.5 text-center text-emerald-500 font-bold">FULL UNLOCKED</td>
                <td class="py-2.5 text-center text-rose-500">TERKUNCI</td>
                <td class="py-2.5 text-center text-rose-500">TERKUNCI</td>
              </tr>
              <tr>
                <td class="py-2.5">Manajemen Pengguna & Akun</td>
                <td class="py-2.5 text-center text-emerald-500 font-bold">FULL ACCESS</td>
                <td class="py-2.5 text-center text-emerald-500 font-bold">FULL ACCESS</td>
                <td class="py-2.5 text-center text-slate-400">Self Only</td>
              </tr>
              <tr>
                <td class="py-2.5">Cluster Orchestration & Nodes</td>
                <td class="py-2.5 text-center text-emerald-500 font-bold">FULL ACCESS</td>
                <td class="py-2.5 text-center text-rose-500">TERKUNCI</td>
                <td class="py-2.5 text-center text-rose-500">TERKUNCI</td>
              </tr>
              <tr>
                <td class="py-2.5">Audit Log & Security Telemetry</td>
                <td class="py-2.5 text-center text-emerald-500 font-bold">SEMUA USER</td>
                <td class="py-2.5 text-center text-sky-400">USER LEVEL</td>
                <td class="py-2.5 text-center text-slate-400">Self Activity</td>
              </tr>
              <tr>
                <td class="py-2.5">Self-Service Profile & MFA</td>
                <td class="py-2.5 text-center text-emerald-500 font-bold">YA</td>
                <td class="py-2.5 text-center text-emerald-500 font-bold">YA</td>
                <td class="py-2.5 text-center text-emerald-500 font-bold">YA</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] font-mono flex items-center justify-between">
          <span class="text-slate-500 dark:text-slate-400">Status Sesi Anda Saat Ini:</span>
          <span v-if="authStore.isSuperadmin" class="text-rose-500 font-bold">Superadmin (Mas Kukuh)</span>
          <span v-else-if="authStore.isAdminAccount" class="text-sky-400 font-bold">Admin Account (DevOps)</span>
          <span v-else-if="authStore.isUser" class="text-slate-300 font-bold">Standard User</span>
          <span v-else class="text-slate-500">Guest / Belum Login</span>
        </div>
      </div>

    </div>

    <!-- Coroutine Performance Benchmarks Card -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
            <Zap class="w-3.5 h-3.5" />
            Performance Breakthrough
          </div>
          <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1">
            Hypervel Coroutine Engine vs Traditional PHP-FPM
          </h3>
        </div>
        <Tag value="16.8x Throughput" severity="success" class="!text-xs self-start sm:self-auto" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div
          v-for="(bench, idx) in clusterStore.benchmarks"
          :key="idx"
          class="p-5 rounded-2xl bg-white/60 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 space-y-3"
        >
          <div class="flex items-center justify-between">
            <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">{{ bench.metric }}</span>
            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded bg-emerald-500/10">
              {{ bench.improvement }}
            </span>
          </div>

          <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ bench.title }}</h4>

          <div class="space-y-1.5 font-mono text-xs">
            <div class="flex justify-between items-center p-2 rounded-lg bg-indigo-50/80 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-900/40">
              <span class="text-indigo-600 dark:text-sky-300 font-semibold">Hypervel (Coroutine):</span>
              <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ bench.hypervel }}</span>
            </div>
            
            <div class="flex justify-between items-center p-2 rounded-lg bg-slate-100 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400">
              <span>PHP-FPM (Legacy):</span>
              <span>{{ bench.laravelFpm }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
