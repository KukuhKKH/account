<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import UserAvatar from '~/components/UserAvatar.vue'
import { useToast } from 'primevue/usetoast'
import {
  User,
  KeyRound,
  Lock,
  ShieldCheck,
  Smartphone,
  Plus,
  AlertCircle,
  CheckCircle2,
  Loader2,
  Info,
  Key
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

const { user, authStore, updateProfile, changePassword } = useAuth()
const toast = useToast()

// Profile Form State
const profileName = ref(user.value?.name || '')
const profileEmail = ref(user.value?.email || '')
const profilePhone = ref(user.value?.phone || '')
const isSavingProfile = ref(false)

// Keep in sync when user session loads
watch(user, (newUser) => {
  if (newUser) {
    if (!profileName.value) profileName.value = newUser.name || ''
    if (!profileEmail.value) profileEmail.value = newUser.email || ''
    if (!profilePhone.value) profilePhone.value = newUser.phone || ''
  }
}, { immediate: true })

// Password Form State
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const isChangingPassword = ref(false)
const passwordError = ref<string | null>(null)

// Password mismatch check
const passwordMismatch = computed(() => {
  return confirmPassword.value.length > 0 && newPassword.value !== confirmPassword.value
})

async function handleSaveProfile() {
  if (!profileName.value.trim()) {
    toast.add({
      severity: 'warn',
      summary: 'Validasi',
      detail: 'Nama lengkap tidak boleh kosong.',
      life: 3000
    })
    return
  }

  isSavingProfile.value = true
  try {
    const res = await updateProfile({
      name: profileName.value.trim(),
      phone: profilePhone.value.trim() || undefined
    })

    toast.add({
      severity: 'success',
      summary: 'Profil Diperbarui',
      detail: res.message || 'Data profil Anda berhasil disimpan.',
      life: 4000
    })
  } catch (err: unknown) {
    const msg = err instanceof Error ? err.message : 'Gagal memperbarui profil.'
    toast.add({
      severity: 'error',
      summary: 'Gagal',
      detail: msg,
      life: 4000
    })
  } finally {
    isSavingProfile.value = false
  }
}

async function handleChangePassword() {
  passwordError.value = null

  if (!currentPassword.value) {
    passwordError.value = 'Kata sandi lama wajib dimasukkan untuk verifikasi akun.'
    return
  }

  if (!newPassword.value) {
    passwordError.value = 'Kata sandi baru wajib diisi.'
    return
  }

  if (newPassword.value.length < 8) {
    passwordError.value = 'Kata sandi baru minimal harus terdiri dari 8 karakter.'
    return
  }

  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = 'Konfirmasi kata sandi baru tidak sesuai.'
    return
  }

  isChangingPassword.value = true
  try {
    const res = await changePassword({
      current_password: currentPassword.value,
      new_password: newPassword.value
    })

    toast.add({
      severity: 'success',
      summary: 'Kata Sandi Diperbarui',
      detail: res.message || 'Kata sandi akun Anda berhasil diperbarui dengan aman.',
      life: 5000
    })

    // Reset password fields
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
  } catch (err: unknown) {
    const msg = err instanceof Error ? err.message : 'Gagal memperbarui kata sandi akun.'
    passwordError.value = msg
    toast.add({
      severity: 'error',
      summary: 'Gagal Mengubah Kata Sandi',
      detail: msg,
      life: 5000
    })
  } finally {
    isChangingPassword.value = false
  }
}
</script>

<template>
  <div class="flex-1 flex flex-col min-w-0">
    <DashboardHeader
      title="Profil Pengguna & Kunci Keamanan"
      subtitle="Kelola identitas akun Anda, kata sandi, otentikasi dua faktor, dan kunci passkey WebAuthn"
    />

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-8 overflow-y-auto max-w-4xl">
      
      <!-- Section 1: User Profile & Identity Details -->
      <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800/80 pb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
              <User class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Informasi Identitas & Kontak
              </h2>
              <p class="text-xs text-slate-400">
                Data diri yang terdaftar pada sistem single sign-on BangLipai
              </p>
            </div>
          </div>
        </div>

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
              <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white tracking-tight truncate">
                {{ user?.name }}
              </h3>
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

        <!-- Form Profile Info -->
        <form @submit.prevent="handleSaveProfile" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Nama Lengkap
              </label>
              <InputText
                v-model="profileName"
                class="w-full !px-4 !py-2.5 !rounded-2xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700 focus:!border-indigo-500"
                placeholder="Masukkan nama lengkap Anda..."
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Nomor Telepon
              </label>
              <InputText
                v-model="profilePhone"
                class="w-full !px-4 !py-2.5 !rounded-2xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700 focus:!border-indigo-500 font-mono"
                placeholder="+62 812-xxxx-xxxx"
              />
            </div>
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
            <p class="text-[11px] text-slate-400 mt-1">
              Email dikelola terpusat oleh Logto SSO provider. Hubungi administrator untuk penggantian email utama.
            </p>
          </div>

          <div class="pt-2 flex justify-end">
            <button
              type="submit"
              :disabled="isSavingProfile"
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-sky-600 hover:from-indigo-500 hover:to-sky-500 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all cursor-pointer hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Loader2 v-if="isSavingProfile" class="w-3.5 h-3.5 animate-spin" />
              <span>{{ isSavingProfile ? 'Menyimpan...' : 'Simpan Informasi Profil' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Section 2: Security & Password Management -->
      <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800/80 pb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
              <Lock class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Kunci Keamanan & Ganti Kata Sandi
              </h2>
              <p class="text-xs text-slate-400">
                Ubah kata sandi login untuk akun Anda sendiri
              </p>
            </div>
          </div>
        </div>

        <!-- Security Rule Banner -->
        <div class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-xs text-blue-700 dark:text-blue-300 flex items-start gap-3">
          <Info class="w-4 h-4 shrink-0 text-blue-500 mt-0.5" />
          <div class="space-y-1">
            <p class="font-bold">Ketentuan Keamanan Akun Pribadi</p>
            <p class="text-[11px] text-blue-600/90 dark:text-blue-300/80 leading-relaxed">
              Demi keamanan akun, perubahan kata sandi akun Anda sendiri <strong>wajib memasukkan kata sandi lama</strong> sebagai pembuktian identitas sah. Reset instan tanpa kata sandi lama hanya dapat dilakukan oleh Administrator terhadap akun pengguna lain di menu Direktori Pengguna.
            </p>
          </div>
        </div>

        <!-- Error Feedback Alert -->
        <div v-if="passwordError" class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-600 dark:text-rose-400 flex items-center gap-2.5">
          <AlertCircle class="w-4 h-4 shrink-0 text-rose-500" />
          <span class="font-semibold">{{ passwordError }}</span>
        </div>

        <!-- Form Password Change -->
        <form @submit.prevent="handleChangePassword" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
              Kata Sandi Lama (Saat Ini) <span class="text-rose-500">*</span>
            </label>
            <Password
              v-model="currentPassword"
              toggleMask
              :feedback="false"
              placeholder="Masukkan kata sandi lama Anda..."
              class="w-full"
              inputClass="w-full !px-4 !py-2.5 !rounded-2xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700 focus:!border-indigo-500 font-mono"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Kata Sandi Baru <span class="text-rose-500">*</span>
              </label>
              <Password
                v-model="newPassword"
                toggleMask
                placeholder="Minimal 8 karakter baru..."
                class="w-full"
                inputClass="w-full !px-4 !py-2.5 !rounded-2xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700 focus:!border-indigo-500 font-mono"
              />
              <p class="text-[10px] text-slate-400 mt-1">Minimal 8 karakter kombinasi huruf dan angka.</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
              </label>
              <Password
                v-model="confirmPassword"
                toggleMask
                :feedback="false"
                placeholder="Ketik ulang kata sandi baru..."
                class="w-full"
                :class="{ '!border-rose-500': passwordMismatch }"
                inputClass="w-full !px-4 !py-2.5 !rounded-2xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700 focus:!border-indigo-500 font-mono"
              />
              <p v-if="passwordMismatch" class="text-[10px] text-rose-500 mt-1 font-semibold">
                Konfirmasi kata sandi tidak cocok.
              </p>
            </div>
          </div>

          <div class="pt-2 flex justify-end">
            <button
              type="submit"
              :disabled="isChangingPassword || !currentPassword || !newPassword || !confirmPassword || passwordMismatch"
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-gradient-to-r from-amber-600 to-rose-600 hover:from-amber-500 hover:to-rose-500 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition-all cursor-pointer hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Loader2 v-if="isChangingPassword" class="w-3.5 h-3.5 animate-spin" />
              <KeyRound v-else class="w-3.5 h-3.5" />
              <span>{{ isChangingPassword ? 'Memperbarui...' : 'Perbarui Kata Sandi Akun' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Section 3: WebAuthn & Hardware Security Keys -->
      <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800/80 pb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
              <ShieldCheck class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Kunci Keamanan WebAuthn / Passkey
              </h3>
              <p class="text-xs text-slate-400 mt-0.5">
                Perangkat keras terotentikasi FIDO2 untuk login cepat tanpa kata sandi
              </p>
            </div>
          </div>

          <button
            type="button"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-indigo-600 dark:text-sky-400 cursor-pointer"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Daftar Kunci Baru</span>
          </button>
        </div>

        <div class="space-y-2.5 pt-2">
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

    </main>
  </div>
</template>
