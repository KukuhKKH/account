<script setup lang="ts">
import type { UserRecord } from '~/composables/useUsers'
import {
  KeyRound,
  Eye,
  EyeOff,
  Copy,
  Check,
  RefreshCw,
  ShieldAlert,
  AlertCircle,
  ShieldCheck,
  Sparkles,
  ExternalLink
} from 'lucide-vue-next'

const props = defineProps<{
  modelValue: boolean
  user: UserRecord | null
  currentUserId?: string
  isSuperadmin: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'passwordReset'): void
}>()

const { resetPassword } = useUsers()
const toast = useToast()

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const isSubmitting = ref(false)
const errorMessage = ref<string | null>(null)
const showPassword = ref(false)
const copiedPassword = ref(false)

const form = reactive({
  passwordMode: 'auto' as 'auto' | 'manual',
  autoPassword: '',
  manualPassword: '',
  reason: ''
})

// Web Crypto secure password generator (16 karakter aman: uppercase, lowercase, numbers, symbols)
function generateSecurePassword(): string {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*'
  let result = ''
  const randomValues = new Uint32Array(16)
  if (typeof window !== 'undefined' && window.crypto) {
    window.crypto.getRandomValues(randomValues)
    for (let i = 0; i < 16; i++) {
      const val = randomValues[i] ?? 0
      result += chars.charAt(val % chars.length)
    }
  } else {
    for (let i = 0; i < 16; i++) {
      result += chars.charAt(Math.floor(Math.random() * chars.length))
    }
  }
  return result
}

function refreshAutoPassword() {
  form.autoPassword = generateSecurePassword()
}

function copyPassword() {
  const pwd = form.passwordMode === 'auto' ? form.autoPassword : form.manualPassword
  if (!pwd) return

  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(pwd)
    copiedPassword.value = true
    setTimeout(() => {
      copiedPassword.value = false
    }, 2000)
  }
}

// Inisialisasi password saat modal dibuka
watch(
  () => props.modelValue,
  (val) => {
    if (val) {
      form.passwordMode = 'auto'
      form.manualPassword = ''
      form.reason = ''
      form.autoPassword = generateSecurePassword()
      showPassword.value = false
      errorMessage.value = null
    }
  }
)

// Proteksi hak akses & anti-self reset
const isSelf = computed(() => {
  return Boolean(props.user && props.currentUserId && String(props.user.id) === String(props.currentUserId))
})

const isTargetElevated = computed(() => {
  return props.user && (props.user.role === 'Superadmin' || props.user.role === 'Admin Account')
})

const canReset = computed(() => {
  if (isSelf.value) return false
  if (props.isSuperadmin) return true
  // Admin hanya boleh mereset Regular User
  if (isTargetElevated.value) return false
  return true
})

async function onSubmit() {
  if (!props.user || !canReset.value) return

  const passwordToSend = form.passwordMode === 'auto' ? form.autoPassword : form.manualPassword

  if (!passwordToSend || passwordToSend.length < 8) {
    errorMessage.value = 'Kata sandi baru minimal harus terdiri dari 8 karakter.'
    return
  }

  isSubmitting.value = true
  errorMessage.value = null

  try {
    const res = await resetPassword(props.user.id, {
      password: passwordToSend,
      reason: form.reason.trim() || undefined
    })

    toast.add({
      severity: 'success',
      summary: 'Kata Sandi Diperbarui',
      detail: res.message || `Kata sandi untuk ${props.user.name} berhasil diperbarui.`,
      life: 4000
    })

    emit('passwordReset')
    visible.value = false
  } catch (err: unknown) {
    const e = err as Error
    errorMessage.value = e.message || 'Terjadi kesalahan saat memperbarui kata sandi.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <Dialog
    v-model:visible="visible"
    modal
    :dismissableMask="true"
    class="w-full max-w-lg mx-4 !rounded-3xl overflow-hidden shadow-2xl !backdrop-blur-xl !border !border-indigo-200 dark:!border-indigo-950/80 !bg-white/95 dark:!bg-slate-950/95"
    :pt="{
      header: { class: '!p-6 !border-b !border-indigo-100 dark:!border-indigo-950/50 !bg-gradient-to-b !from-indigo-50/50 dark:!from-indigo-950/20 !to-transparent' },
      content: { class: '!p-6 space-y-5' },
      footer: { class: '!p-5 !border-t !border-slate-100 dark:!border-slate-800/80 !bg-slate-50/50 dark:!bg-slate-900/40 flex justify-end gap-3' }
    }"
  >
    <!-- Header -->
    <template #header>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-500/20">
          <KeyRound class="w-5 h-5" />
        </div>
        <div>
          <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">
            Reset Kata Sandi Pengguna
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">
            Perbarui kredensial akun dan sinkronkan dengan Logto SSO Management
          </p>
        </div>
      </div>
    </template>

    <div class="space-y-4 text-xs">
      
      <!-- Error Alert -->
      <div v-if="errorMessage" class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-300 flex items-start gap-2">
        <AlertCircle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
        <p class="text-xs">{{ errorMessage }}</p>
      </div>

      <!-- Self-Reset Barrier Alert -->
      <div v-if="isSelf" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/60 text-amber-900 dark:text-amber-200 space-y-2">
        <div class="font-bold flex items-center gap-1.5 text-xs text-amber-700 dark:text-amber-300">
          <ShieldAlert class="w-4 h-4 text-amber-500 shrink-0" />
          <span>Akun Pribadi Terdeteksi</span>
        </div>
        <p class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-300">
          Anda tidak dapat mereset kata sandi akun Anda sendiri dari menu manajemen direktori. Demi keamanan akun, perubahan kata sandi pribadi wajib dilakukan melalui menu <strong>Profil Pengguna & Kunci Keamanan</strong> dengan memverifikasi kata sandi lama Anda.
        </p>
        <div class="pt-1">
          <NuxtLink
            to="/dashboard/profile"
            @click="visible = false"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-xs transition-all cursor-pointer"
          >
            <span>Buka Profil Pengguna</span>
            <ExternalLink class="w-3.5 h-3.5" />
          </NuxtLink>
        </div>
      </div>

      <!-- Permission Barrier Alert -->
      <div v-else-if="!canReset" class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-300 space-y-1">
        <div class="font-bold flex items-center gap-1.5">
          <ShieldAlert class="w-4 h-4 text-rose-500" />
          <span>Akses Terbatas (Privilege Boundary)</span>
        </div>
        <p class="text-[11px]">
          Anda tidak memiliki wewenang untuk mereset kata sandi akun Admin atau Superadmin. Tindakan ini dibatasi oleh Kebijakan Keamanan Sistem.
        </p>
      </div>

      <!-- Target Account Card -->
      <div v-if="user" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-3.5">
        <UserAvatar
          :name="user.name"
          :email="user.email"
          :avatar="user.avatar"
          :role="user.role"
          size="md"
          class="shadow-sm"
        />
        <div class="min-w-0 flex-1">
          <p class="font-bold text-slate-900 dark:text-white text-xs truncate flex items-center gap-1.5">
            <span>{{ user.name }}</span>
            <Sparkles v-if="user.role === 'Superadmin'" class="w-3.5 h-3.5 text-amber-500" />
          </p>
          <p class="font-mono text-[10px] text-slate-400 truncate mt-0.5">
            {{ user.email }} • ID: {{ user.id }}
          </p>
          <div class="mt-1.5 flex items-center gap-2">
            <Tag
              :value="user.role"
              :severity="user.role === 'Superadmin' ? 'danger' : (user.role === 'Admin Account' ? 'info' : 'secondary')"
              class="!text-[9px] !font-bold"
            />
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono"
              :class="user.status === 'active' 
                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' 
                : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'"
            >
              {{ user.status === 'active' ? 'Aktif' : 'Suspended' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Password Configuration Section -->
      <div v-if="canReset" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/40 space-y-3.5">
        
        <div class="flex items-center justify-between">
          <label class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
            <KeyRound class="w-3.5 h-3.5 text-indigo-500" />
            <span>Kata Sandi Baru</span>
          </label>
          
          <!-- Mode Tabs -->
          <div class="flex items-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[11px]">
            <button
              type="button"
              @click="form.passwordMode = 'auto'"
              class="px-2.5 py-1 rounded-lg font-medium transition-all cursor-pointer"
              :class="form.passwordMode === 'auto' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-500'"
            >
              Acak Otomatis
            </button>
            <button
              type="button"
              @click="form.passwordMode = 'manual'"
              class="px-2.5 py-1 rounded-lg font-medium transition-all cursor-pointer"
              :class="form.passwordMode === 'manual' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-500'"
            >
              Ketik Manual
            </button>
          </div>
        </div>

        <!-- Auto-Generated Display -->
        <div v-if="form.passwordMode === 'auto'" class="flex items-center gap-2">
          <div class="flex-1 px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-950 font-mono text-xs border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 flex items-center justify-between">
            <span class="tracking-wider">{{ showPassword ? form.autoPassword : '••••••••••••••••' }}</span>
            <div class="flex items-center gap-1">
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                title="Lihat / Sembunyikan"
              >
                <Eye v-if="!showPassword" class="w-3.5 h-3.5" />
                <EyeOff v-else class="w-3.5 h-3.5" />
              </button>
              <button
                type="button"
                @click="refreshAutoPassword"
                class="p-1 rounded text-slate-400 hover:text-indigo-600 dark:hover:text-sky-400 cursor-pointer"
                title="Buat Sandi Acak Baru"
              >
                <RefreshCw class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <button
            type="button"
            @click="copyPassword"
            class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-all flex items-center gap-1.5 cursor-pointer shrink-0 font-medium"
          >
            <Check v-if="copiedPassword" class="w-3.5 h-3.5 text-emerald-500" />
            <Copy v-else class="w-3.5 h-3.5" />
            <span>{{ copiedPassword ? 'Tersalin' : 'Salin' }}</span>
          </button>
        </div>

        <!-- Manual Input -->
        <div v-else class="space-y-1.5">
          <div class="relative">
            <InputText
              v-model="form.manualPassword"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Minimal 8 karakter..."
              class="w-full !pr-10 !rounded-xl !text-xs !bg-slate-50 dark:!bg-slate-950 !border-slate-200 dark:!border-slate-800 font-mono"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
            >
              <Eye v-if="!showPassword" class="w-4 h-4" />
              <EyeOff v-else class="w-4 h-4" />
            </button>
          </div>
          <p class="text-[10px] text-slate-400">
            Pastikan kata sandi memuat kombinasi huruf besar, huruf kecil, angka, dan karakter khusus.
          </p>
        </div>

        <!-- Reason Input for Security Audit -->
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-1">
          <label class="font-medium text-slate-700 dark:text-slate-300 text-[11px]">
            Alasan Pembaruan (Catatan Audit Keamanan)
          </label>
          <InputText
            v-model="form.reason"
            placeholder="Contoh: Permintaan reset kata sandi via tiket helpdesk #102..."
            class="w-full !rounded-xl !text-xs !bg-slate-50 dark:!bg-slate-950 !border-slate-200 dark:!border-slate-800"
          />
        </div>

      </div>

      <!-- Sync Notification Banner -->
      <div v-if="canReset" class="p-3.5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/40 text-indigo-900 dark:text-sky-300 flex items-start gap-2.5">
        <ShieldCheck class="w-4 h-4 text-indigo-600 dark:text-sky-400 shrink-0 mt-0.5" />
        <div class="text-[11px] leading-relaxed">
          <span class="font-bold">Sinkronisasi Logto M2M:</span> Kata sandi baru akan langsung disinkronkan ke Central DB dan Logto SSO Engine. Riwayat perubahan dicatat ke tabel audit log untuk keamanan.
        </div>
      </div>

    </div>

    <!-- Footer -->
    <template #footer>
      <button
        type="button"
        @click="visible = false"
        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
      >
        Batal
      </button>

      <button
        type="button"
        @click="onSubmit"
        :disabled="!canReset || isSubmitting"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-sky-600 hover:from-indigo-500 hover:to-sky-500 text-white font-bold text-xs shadow-md shadow-indigo-500/25 transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
      >
        <RefreshCw v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" />
        <KeyRound v-else class="w-3.5 h-3.5" />
        <span>{{ isSubmitting ? 'Menyimpan...' : 'Perbarui Kata Sandi' }}</span>
      </button>
    </template>
  </Dialog>
</template>
