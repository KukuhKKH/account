<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import UserAvatar from '~/components/UserAvatar.vue'
import {
  User,
  KeyRound,
  Lock,
  CheckCircle2,
  ShieldCheck,
  Smartphone,
  Plus,
  Trash2
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

const { user, authStore } = useAuth()

const profileName = ref(user.value?.name || '')
const profileEmail = ref(user.value?.email || '')
const profilePassword = ref('')
const profileToastMessage = ref('')

function saveProfile() {
  if (authStore.currentUser) {
    authStore.currentUser.name = profileName.value
    profileToastMessage.value = 'Profil dan pengaturan keamanan berhasil disimpan!'
    setTimeout(() => {
      profileToastMessage.value = ''
    }, 4000)
  }
}
</script>

<template>
  <div class="flex-1 flex flex-col min-w-0">
    <DashboardHeader
      title="Profil Pengguna & Kunci Keamanan"
      subtitle="Kelola identitas akun Anda, kata sandi, otentikasi dua faktor, dan kunci passkey WebAuthn"
    />

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 overflow-y-auto">
      
      <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-8 max-w-3xl">
        
        <!-- Visual Profile Avatar Card -->
        <div class="flex items-center gap-4 p-4 sm:p-5 rounded-3xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50">
          <UserAvatar
            :name="user?.name"
            :email="user?.email"
            :avatar="user?.avatarUrl"
            :role="user?.role"
            size="xl"
            class="shadow-lg"
          />
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white tracking-tight truncate">
                {{ user?.name }}
              </h2>
              <Tag
                :value="user?.role"
                :severity="authStore.isSuperadmin ? 'danger' : (authStore.isAdminAccount ? 'info' : 'secondary')"
                class="!text-[10px]"
              />
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5 truncate">
              {{ user?.email }}
            </p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              <span>Avatar tersinkronisasi via <span class="font-mono text-indigo-500">avatar.banglipai.web.id</span></span>
            </p>
          </div>
        </div>

        <!-- Success Alert -->
        <div v-if="profileToastMessage" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-2.5 animate-in fade-in">
          <CheckCircle2 class="w-4 h-4 shrink-0" />
          <span class="font-semibold">{{ profileToastMessage }}</span>
        </div>

        <!-- Form Profile -->
        <form @submit.prevent="saveProfile" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Nama Lengkap
            </label>
            <InputText
              v-model="profileName"
              class="w-full !px-4 !py-2.5 !rounded-2xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700 focus:!border-indigo-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Email SSO Terdaftar
            </label>
            <InputText
              v-model="profileEmail"
              disabled
              class="w-full !px-4 !py-2.5 !rounded-2xl !text-xs !bg-slate-100 dark:!bg-slate-800 !border-slate-300 dark:!border-slate-700 !opacity-70 font-mono"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Ganti Password Baru (Opsional)
            </label>
            <Password
              v-model="profilePassword"
              toggleMask
              placeholder="Ketik password baru jika ingin mengubah..."
              class="w-full"
              inputClass="w-full !px-4 !py-2.5 !rounded-2xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700 font-mono"
            />
          </div>

          <div class="pt-2">
            <button
              type="submit"
              class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-sky-600 hover:from-indigo-500 hover:to-sky-500 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all cursor-pointer hover:-translate-y-0.5"
            >
              Simpan Perubahan
            </button>
          </div>
        </form>

        <!-- WebAuthn & Security Keys -->
        <div class="border-t border-slate-200/80 dark:border-slate-800/80 pt-6 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Kunci Keamanan WebAuthn / Passkey
              </h3>
              <p class="text-[11px] text-slate-400 mt-0.5">
                Perangkat keras terotentikasi untuk login tanpa kata sandi
              </p>
            </div>

            <button
              type="button"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-indigo-600 dark:text-sky-400 cursor-pointer"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>Daftar Kunci</span>
            </button>
          </div>

          <div class="space-y-2.5">
            <div class="p-4 rounded-2xl bg-white/70 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800/80 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                  <KeyRound class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-xs font-bold text-slate-900 dark:text-white">YubiKey 5 NFC / Mac Touch ID</p>
                  <p class="text-[10px] text-slate-400 font-mono">ID: key_98af...21 • Terdaftar 12 Sep 2026</p>
                </div>
              </div>
              <Tag value="Active" severity="success" class="!text-[10px]" />
            </div>
          </div>
        </div>

      </div>

    </main>
  </div>
</template>
