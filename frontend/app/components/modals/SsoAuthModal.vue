<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import type { UserRoleType } from '~/types/auth'
import {
  ShieldCheck,
  Lock,
  UserCheck,
  Cpu,
  KeyRound,
  X
} from 'lucide-vue-next'

const authStore = useAuthStore()

const emailInput = ref('kukuh@banglipai.web.id')
const passwordInput = ref('••••••••••••')
const selectedRole = ref<UserRoleType>('Superadmin')

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
    :style="{ width: '90vw', maxWidth: '480px' }"
    :pt="{
      root: { class: '!rounded-3xl !border !border-slate-200/80 dark:!border-slate-800/80 !bg-white/95 dark:!bg-slate-950/95 !backdrop-blur-2xl !p-0 !overflow-hidden !shadow-2xl' },
      mask: { class: '!backdrop-blur-md !bg-slate-950/60 dark:!bg-slate-950/75' },
      header: { class: '!hidden' },
      closeButton: { class: '!hidden' },
      content: { class: '!p-0' }
    }"
  >
    <div class="relative p-6 sm:p-8 space-y-6">
      
      <!-- Close Button -->
      <button
        @click="authStore.closeSsoModal()"
        class="absolute top-5 right-5 p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
      >
        <X class="w-4 h-4" />
      </button>

      <!-- Modal Header -->
      <div class="text-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-sky-500 p-0.5 mx-auto shadow-lg shadow-indigo-500/25">
          <div class="w-full h-full bg-white dark:bg-slate-900 rounded-[14px] flex items-center justify-center">
            <ShieldCheck class="w-6 h-6 text-indigo-600 dark:text-sky-400" />
          </div>
        </div>
        <h3 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
          BangLipai Secure Portal
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
          Single Sign-On Engine & Delegated Access Governance
        </p>
      </div>

      <!-- Primary Action: Connect via Logto SSO (BFF) -->
      <div class="space-y-3">
        <button
          type="button"
          @click="authStore.redirectToLogin()"
          class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-indigo-600 via-sky-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/40 transition-all cursor-pointer flex items-center justify-center gap-2 hover:-translate-y-0.5"
        >
          <ShieldCheck class="w-4 h-4" />
          <span>Masuk via Logto SSO (BFF Central Engine)</span>
        </button>
        <p class="text-[10px] text-center text-slate-400">
          Mengarahkan langsung ke SSO Auth Provider <span class="font-mono text-indigo-500 dark:text-sky-400">/auth/login</span>
        </p>
      </div>

      <!-- Divider -->
      <div class="relative flex py-1 items-center">
        <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
        <span class="flex-shrink mx-3 text-[10px] uppercase font-mono text-slate-400">atau Simulasi Dev</span>
        <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
      </div>

      <!-- 1-Click Role Switcher Simulation -->
      <div class="space-y-2">
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
          Simulasi Peran Lokal (Dev Mode):
        </label>
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
      </div>

      <!-- Quick Login Simulation Button -->
      <button
        type="button"
        @click="submitLogin"
        :disabled="authStore.isLoading"
        class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs border border-slate-200 dark:border-slate-700 transition-all cursor-pointer flex items-center justify-center gap-2"
      >
        <UserCheck class="w-4 h-4 text-indigo-500" />
        <span>{{ authStore.isLoading ? 'Memproses Sesi...' : `Masuk Simulasi Sebagai ${selectedRole}` }}</span>
      </button>

      <!-- Security Notice -->
      <div class="border-t border-slate-200/80 dark:border-slate-800/80 pt-4 flex items-center justify-center gap-2 text-[11px] text-slate-400 font-mono">
        <Lock class="w-3.5 h-3.5 text-emerald-500" />
        <span>BFF Session Encrypted • Zero Token Exposure</span>
      </div>

    </div>
  </Dialog>
</template>
