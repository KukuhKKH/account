<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import type { UserRoleType } from '~/types/auth'
import {
  ShieldCheck,
  Lock,
  UserCheck,
  Sparkles,
  ArrowRight,
  User,
  X,
  Fingerprint
} from 'lucide-vue-next'

const authStore = useAuthStore()

const emailInput = ref('kukuh@banglipai.web.id')
const passwordInput = ref('••••••••••••')
const selectedRole = ref<UserRoleType>('Superadmin')
const showSimulatedSwitch = ref(false)

function selectRole(role: UserRoleType) {
  selectedRole.value = role
  if (role === 'Superadmin') {
    emailInput.value = 'kukuh@banglipai.web.id'
  } else if (role === 'Admin Account') {
    emailInput.value = 'admin@banglipai.web.id'
  } else {
    emailInput.value = 'user@banglipai.web.id'
  }
}

function submitLogin() {
  authStore.login({
    email: emailInput.value,
    password: passwordInput.value,
    role: selectedRole.value
  })
}
</script>

<template>
  <Dialog
    v-model:visible="authStore.ssoDialogVisible"
    modal
    :dismissableMask="true"
    :showHeader="false"
    :closable="false"
    :style="{ width: '92vw', maxWidth: '460px' }"
    :pt="{
      root: { class: '!rounded-3xl !border !border-slate-200/80 dark:!border-slate-800/80 !bg-white/95 dark:!bg-slate-950/95 !backdrop-blur-2xl !p-0 !overflow-hidden !shadow-2xl' },
      mask: { class: '!backdrop-blur-md !bg-slate-950/60 dark:!bg-slate-950/80' },
      header: { class: '!hidden' },
      closeButton: { class: '!hidden' },
      content: { class: '!p-0' }
    }"
  >
    <div class="relative p-6 sm:p-8 space-y-6">
      
      <!-- Top Ambient Decorative Glow -->
      <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-64 h-32 bg-indigo-500/20 dark:bg-indigo-500/25 blur-3xl pointer-events-none"></div>

      <!-- Close Button -->
      <button
        @click="authStore.closeSsoModal()"
        class="absolute top-4 right-4 p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-all cursor-pointer z-10"
        title="Tutup"
        aria-label="Tutup"
      >
        <X class="w-4 h-4" />
      </button>

      <!-- Modal Brand Header -->
      <div class="text-center space-y-3 pt-1">
        <div class="relative inline-flex">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-sky-500 to-emerald-400 p-[2px] shadow-xl shadow-indigo-500/25 mx-auto">
            <div class="w-full h-full bg-white dark:bg-slate-900 rounded-[14px] flex items-center justify-center">
              <ShieldCheck class="w-7 h-7 text-indigo-600 dark:text-sky-400" />
            </div>
          </div>
          <span class="absolute -bottom-1 -right-1 flex h-4 w-4">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white dark:border-slate-950"></span>
          </span>
        </div>

        <div>
          <div class="flex items-center justify-center gap-1.5">
            <h3 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
              BangLipai Secure Portal
            </h3>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Centralized Single Sign-On & Access Governance
          </p>
        </div>
      </div>

      <!-- Current Active Session Card (if user is already logged in) -->
      <div v-if="authStore.isLoggedIn" class="p-3.5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/60 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-sky-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
            {{ authStore.currentUser?.name.charAt(0) || 'U' }}
          </div>
          <div class="min-w-0">
            <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
              {{ authStore.currentUser?.name }}
            </p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate font-mono">
              {{ authStore.currentUser?.email }}
            </p>
          </div>
        </div>

        <Tag
          :value="authStore.currentUser?.role"
          :severity="authStore.isSuperadmin ? 'danger' : (authStore.isAdminAccount ? 'info' : 'secondary')"
          class="!text-[10px] shrink-0 font-semibold"
        />
      </div>

      <!-- Primary Action: Authenticate via BangLipai Secure Portal (SSO Live) -->
      <div class="space-y-3">
        <button
          type="button"
          @click="authStore.redirectToLogin()"
          class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-sky-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 text-white font-bold text-xs shadow-lg shadow-indigo-600/25 hover:shadow-indigo-600/35 transition-all cursor-pointer flex items-center justify-center gap-2.5 hover:-translate-y-0.5 group"
        >
          <Fingerprint class="w-4 h-4 text-white/90 group-hover:scale-110 transition-transform" />
          <span>{{ authStore.isLoggedIn ? 'Ganti Akun via BangLipai Secure Portal' : 'Masuk via BangLipai Secure Portal' }}</span>
          <ArrowRight class="w-3.5 h-3.5 text-white/80 group-hover:translate-x-0.5 transition-transform" />
        </button>

        <p class="text-[11px] text-center text-slate-400 dark:text-slate-500 flex items-center justify-center gap-1.5 font-mono">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
          <span>Live SSO Engine • Endpoint: <span class="text-indigo-600 dark:text-sky-400">/auth/login</span></span>
        </p>
      </div>

      <!-- Simulated Dev Switcher (Collapsible for Clean Look) -->
      <div class="border-t border-slate-200/80 dark:border-slate-800/80 pt-3">
        <button
          type="button"
          @click="showSimulatedSwitch = !showSimulatedSwitch"
          class="w-full flex items-center justify-between text-[11px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors py-1 cursor-pointer"
        >
          <span class="font-mono flex items-center gap-1.5">
            <Sparkles class="w-3 h-3 text-amber-500" />
            <span>Simulasi Peran Dev Mode</span>
          </span>
          <span class="text-[10px] text-indigo-500 font-semibold">
            {{ showSimulatedSwitch ? 'Sembunyikan' : 'Tampilkan' }}
          </span>
        </button>

        <div v-if="showSimulatedSwitch" class="space-y-3 pt-3 animate-in fade-in duration-150">
          <div class="grid grid-cols-3 gap-2">
            
            <button
              type="button"
              @click="selectRole('Superadmin')"
              class="p-2.5 rounded-xl border text-left text-xs transition-all cursor-pointer"
              :class="selectedRole === 'Superadmin' 
                ? 'border-rose-500 bg-rose-50/80 dark:bg-rose-950/40 text-rose-900 dark:text-rose-200 ring-2 ring-rose-500/20 font-bold' 
                : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400'"
            >
              <div class="flex items-center gap-1 text-[11px]">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                <span>Superadmin</span>
              </div>
              <p class="text-[10px] text-slate-500 truncate mt-0.5">Mas Kukuh</p>
            </button>

            <button
              type="button"
              @click="selectRole('Admin Account')"
              class="p-2.5 rounded-xl border text-left text-xs transition-all cursor-pointer"
              :class="selectedRole === 'Admin Account' 
                ? 'border-sky-500 bg-sky-50/80 dark:bg-sky-950/40 text-sky-900 dark:text-sky-200 ring-2 ring-sky-500/20 font-bold' 
                : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400'"
            >
              <div class="flex items-center gap-1 text-[11px]">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                <span>Admin</span>
              </div>
              <p class="text-[10px] text-slate-500 truncate mt-0.5">DevOps Hub</p>
            </button>

            <button
              type="button"
              @click="selectRole('User')"
              class="p-2.5 rounded-xl border text-left text-xs transition-all cursor-pointer"
              :class="selectedRole === 'User' 
                ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 ring-2 ring-indigo-500/20 font-bold' 
                : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400'"
            >
              <div class="flex items-center gap-1 text-[11px]">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                <span>Member</span>
              </div>
              <p class="text-[10px] text-slate-500 truncate mt-0.5">User Biasa</p>
            </button>

          </div>

          <button
            type="button"
            @click="submitLogin"
            :disabled="authStore.isLoading"
            class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs border border-slate-200 dark:border-slate-700 transition-all cursor-pointer flex items-center justify-center gap-2"
          >
            <UserCheck class="w-4 h-4 text-indigo-500" />
            <span>{{ authStore.isLoading ? 'Memproses Sesi...' : `Beralih Sebagai ${selectedRole}` }}</span>
          </button>
        </div>
      </div>

      <!-- Security Trust Footer -->
      <div class="border-t border-slate-200/80 dark:border-slate-800/80 pt-4 flex items-center justify-center gap-2 text-[11px] text-slate-400 dark:text-slate-500 font-mono">
        <Lock class="w-3.5 h-3.5 text-emerald-500" />
        <span>BFF Session Encrypted • Zero Token Exposure</span>
      </div>

    </div>
  </Dialog>
</template>
