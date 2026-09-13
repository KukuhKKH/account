<script setup lang="ts">
import type { UserRoleType } from '~/types/auth'
import {
  UserPlus,
  User,
  Mail,
  Phone,
  MapPin,
  ShieldCheck,
  ShieldAlert,
  KeyRound,
  Sparkles,
  Copy,
  Check,
  RefreshCw,
  Eye,
  EyeOff,
  AlertCircle
} from 'lucide-vue-next'

const props = defineProps<{
  modelValue: boolean
  isSuperadmin: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'created'): void
}>()

const { createUser } = useUsers()
const toast = useToast()

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const isSubmitting = ref(false)
const errorMessage = ref<string | null>(null)

// Form State
const form = reactive({
  name: '',
  email: '',
  phone: '',
  address: '',
  role: 'User' as UserRoleType,
  passwordMode: 'auto' as 'auto' | 'manual',
  manualPassword: '',
  autoPassword: ''
})

const showPassword = ref(false)
const copiedPassword = ref(false)

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

watch(visible, (isOpen) => {
  if (isOpen) {
    form.name = ''
    form.email = ''
    form.phone = ''
    form.address = ''
    form.role = 'User'
    form.passwordMode = 'auto'
    form.manualPassword = ''
    form.autoPassword = generateSecurePassword()
    errorMessage.value = null
  }
})

async function onSubmit() {
  if (!form.name.trim() || !form.email.trim()) {
    errorMessage.value = 'Nama lengkap dan alamat email wajib diisi.'
    return
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.email.trim())) {
    errorMessage.value = 'Format alamat email tidak valid.'
    return
  }

  isSubmitting.value = true
  errorMessage.value = null

  const passwordToSend = form.passwordMode === 'auto' ? form.autoPassword : form.manualPassword

  try {
    const res = await createUser({
      name: form.name.trim(),
      email: form.email.trim(),
      role: form.role,
      password: passwordToSend || undefined,
      phone: form.phone.trim() || undefined,
      address: form.address.trim() || undefined
    })

    toast.add({
      severity: 'success',
      summary: 'Pengguna Dibuat',
      detail: res.message || 'Akun berhasil ditambahkan ke direktori SSO.',
      life: 4000
    })

    emit('created')
    visible.value = false
  } catch (err: unknown) {
    const e = err as Error
    errorMessage.value = e.message || 'Gagal membuat pengguna baru.'
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
    class="w-full max-w-xl mx-4 !rounded-3xl overflow-hidden !border !border-slate-200 dark:!border-slate-800 !bg-white/95 dark:!bg-slate-950/95 !backdrop-blur-xl shadow-2xl"
    :pt="{
      header: { class: '!p-6 !border-b !border-slate-100 dark:!border-slate-800/80 !bg-gradient-to-b !from-slate-50 dark:!from-slate-900/50 !to-transparent' },
      content: { class: '!p-6 space-y-6 max-h-[75vh] overflow-y-auto' },
      footer: { class: '!p-5 !border-t !border-slate-100 dark:!border-slate-800/80 !bg-slate-50/50 dark:!bg-slate-900/40 flex justify-end gap-3' }
    }"
  >
    <template #header>
      <div class="flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-sky-500 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25">
          <UserPlus class="w-5 h-5" />
        </div>
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            <span>Tambah Pengguna Baru</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-sky-400 border border-indigo-200 dark:border-indigo-800">
              SSO Engine
            </span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Daftarkan akun identitas baru ke Central DB & Logto M2M
          </p>
        </div>
      </div>
    </template>

    <form @submit.prevent="onSubmit" class="space-y-5 text-xs">
      
      <!-- Error Alert -->
      <div v-if="errorMessage" class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-300 flex items-start gap-2.5">
        <AlertCircle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
        <p class="text-xs font-medium">{{ errorMessage }}</p>
      </div>

      <!-- Identity Fields Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Name Field -->
        <div class="space-y-1.5 sm:col-span-2">
          <label class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
            <User class="w-3.5 h-3.5 text-indigo-500" />
            <span>Nama Lengkap <span class="text-rose-500">*</span></span>
          </label>
          <InputText
            v-model="form.name"
            placeholder="Contoh: Budi Pratama"
            class="w-full !rounded-2xl !text-xs !bg-slate-50/70 dark:!bg-slate-900/70 !border-slate-200 dark:!border-slate-800 focus:!border-indigo-500 !py-2.5"
            required
          />
        </div>

        <!-- Email Field -->
        <div class="space-y-1.5 sm:col-span-2">
          <label class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
            <Mail class="w-3.5 h-3.5 text-sky-500" />
            <span>Alamat Email SSO <span class="text-rose-500">*</span></span>
          </label>
          <InputText
            v-model="form.email"
            type="email"
            placeholder="nama@banglipai.web.id"
            class="w-full !rounded-2xl !text-xs !bg-slate-50/70 dark:!bg-slate-900/70 !border-slate-200 dark:!border-slate-800 focus:!border-indigo-500 !py-2.5 font-mono"
            required
          />
        </div>

        <!-- Phone Field -->
        <div class="space-y-1.5">
          <label class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
            <Phone class="w-3.5 h-3.5 text-emerald-500" />
            <span>Nomor WhatsApp / Phone</span>
          </label>
          <InputText
            v-model="form.phone"
            placeholder="628123456789"
            class="w-full !rounded-2xl !text-xs !bg-slate-50/70 dark:!bg-slate-900/70 !border-slate-200 dark:!border-slate-800 focus:!border-indigo-500 !py-2.5 font-mono"
          />
        </div>

        <!-- Address Field -->
        <div class="space-y-1.5">
          <label class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
            <MapPin class="w-3.5 h-3.5 text-amber-500" />
            <span>Lokasi / Divisi</span>
          </label>
          <InputText
            v-model="form.address"
            placeholder="Contoh: DevOps Lab / R&D"
            class="w-full !rounded-2xl !text-xs !bg-slate-50/70 dark:!bg-slate-900/70 !border-slate-200 dark:!border-slate-800 focus:!border-indigo-500 !py-2.5"
          />
        </div>

      </div>

      <!-- Role Selection Section -->
      <div class="space-y-2 pt-1">
        <label class="font-bold text-slate-700 dark:text-slate-300 flex items-center justify-between">
          <span class="flex items-center gap-1.5">
            <ShieldCheck class="w-3.5 h-3.5 text-indigo-500" />
            <span>Pilih Hak Akses & Peran (RBAC) <span class="text-rose-500">*</span></span>
          </span>
          <span class="text-[10px] text-slate-400 font-normal">Enterprise Access Control</span>
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
          
          <!-- Role: User -->
          <div
            @click="form.role = 'User'"
            class="p-3.5 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between space-y-2 relative overflow-hidden"
            :class="form.role === 'User'
              ? 'border-indigo-500 bg-indigo-500/10 shadow-md shadow-indigo-500/10 ring-2 ring-indigo-500/30'
              : 'border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50'"
          >
            <div>
              <div class="flex items-center justify-between">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">User</span>
                <span class="w-2 h-2 rounded-full" :class="form.role === 'User' ? 'bg-indigo-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
              </div>
              <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                Akses standar SSO portal & aplikasi ekosistem.
              </p>
            </div>
            <span class="text-[9px] font-mono text-indigo-600 dark:text-sky-400 font-semibold">Standard Member</span>
          </div>

          <!-- Role: Admin Account -->
          <div
            @click="isSuperadmin && (form.role = 'Admin Account')"
            class="p-3.5 rounded-2xl border transition-all flex flex-col justify-between space-y-2 relative overflow-hidden"
            :class="[
              !isSuperadmin ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-900/30 border-dashed' : 'cursor-pointer',
              form.role === 'Admin Account'
                ? 'border-sky-500 bg-sky-500/10 shadow-md shadow-sky-500/10 ring-2 ring-sky-500/30'
                : 'border-slate-200 dark:border-slate-800 hover:border-sky-300 dark:hover:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50'
            ]"
          >
            <div>
              <div class="flex items-center justify-between">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center gap-1">
                  <span>Admin</span>
                  <ShieldCheck class="w-3 h-3 text-sky-500" />
                </span>
                <span class="w-2 h-2 rounded-full" :class="form.role === 'Admin Account' ? 'bg-sky-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
              </div>
              <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                Kelola member user & audit log direktori.
              </p>
            </div>
            <span class="text-[9px] font-mono text-sky-600 dark:text-sky-400 font-semibold">
              {{ isSuperadmin ? 'User Management' : 'Superadmin Only' }}
            </span>
          </div>

          <!-- Role: Superadmin -->
          <div
            @click="isSuperadmin && (form.role = 'Superadmin')"
            class="p-3.5 rounded-2xl border transition-all flex flex-col justify-between space-y-2 relative overflow-hidden"
            :class="[
              !isSuperadmin ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-900/30 border-dashed' : 'cursor-pointer',
              form.role === 'Superadmin'
                ? 'border-rose-500 bg-rose-500/10 shadow-md shadow-rose-500/10 ring-2 ring-rose-500/30'
                : 'border-slate-200 dark:border-slate-800 hover:border-rose-300 dark:hover:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50'
            ]"
          >
            <div>
              <div class="flex items-center justify-between">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center gap-1">
                  <span>Superadmin</span>
                  <Sparkles class="w-3 h-3 text-rose-500" />
                </span>
                <span class="w-2 h-2 rounded-full" :class="form.role === 'Superadmin' ? 'bg-rose-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
              </div>
              <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                Akses penuh cluster & manajemen seluruh node.
              </p>
            </div>
            <span class="text-[9px] font-mono text-rose-600 dark:text-rose-400 font-semibold">
              {{ isSuperadmin ? 'Full Power' : 'Superadmin Only' }}
            </span>
          </div>

        </div>
      </div>

      <!-- Password Generation Box -->
      <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-gradient-to-b from-slate-50/60 dark:from-slate-900/60 to-transparent space-y-3">
        <div class="flex items-center justify-between">
          <label class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
            <KeyRound class="w-3.5 h-3.5 text-amber-500" />
            <span>Kata Sandi Akun</span>
          </label>

          <!-- Toggle Auto / Manual -->
          <div class="flex items-center p-0.5 rounded-xl bg-slate-200/80 dark:bg-slate-800 text-[10px] font-semibold">
            <button
              type="button"
              @click="form.passwordMode = 'auto'"
              class="px-2.5 py-1 rounded-lg transition-all cursor-pointer"
              :class="form.passwordMode === 'auto' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500'"
            >
              Auto (Crypto)
            </button>
            <button
              type="button"
              @click="form.passwordMode = 'manual'"
              class="px-2.5 py-1 rounded-lg transition-all cursor-pointer"
              :class="form.passwordMode === 'manual' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500'"
            >
              Manual
            </button>
          </div>
        </div>

        <!-- Mode Auto -->
        <div v-if="form.passwordMode === 'auto'" class="flex items-center gap-2">
          <div class="flex-1 flex items-center justify-between px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-mono text-xs text-indigo-600 dark:text-sky-400">
            <span class="tracking-wider">{{ showPassword ? form.autoPassword : '••••••••••••••••' }}</span>
            <div class="flex items-center gap-1">
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
                title="Tampilkan / Sembunyikan"
              >
                <EyeOff v-if="showPassword" class="w-3.5 h-3.5" />
                <Eye v-else class="w-3.5 h-3.5" />
              </button>
              <button
                type="button"
                @click="refreshAutoPassword"
                class="p-1 rounded-lg text-slate-400 hover:text-indigo-600 dark:hover:text-sky-400 cursor-pointer"
                title="Generate Ulang"
              >
                <RefreshCw class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <button
            type="button"
            @click="copyPassword"
            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-indigo-200 dark:border-indigo-900/60 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-sky-400 font-bold hover:bg-indigo-100 transition-all cursor-pointer"
          >
            <Check v-if="copiedPassword" class="w-3.5 h-3.5 text-emerald-500" />
            <Copy v-else class="w-3.5 h-3.5" />
            <span>{{ copiedPassword ? 'Tersalin' : 'Salin' }}</span>
          </button>
        </div>

        <!-- Mode Manual -->
        <div v-else class="relative">
          <InputText
            v-model="form.manualPassword"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Masukkan kata sandi aman (min 8 karakter)..."
            class="w-full !rounded-xl !text-xs !bg-slate-50/70 dark:!bg-slate-900/70 !border-slate-200 dark:!border-slate-800 !pr-10 !py-2"
          />
          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 cursor-pointer"
          >
            <EyeOff v-if="showPassword" class="w-4 h-4" />
            <Eye v-else class="w-4 h-4" />
          </button>
        </div>

        <p class="text-[10px] text-slate-400">
          Pengguna dapat memperbarui kata sandi mandiri atau mengaktifkan Passkey OIDC setelah login pertama.
        </p>
      </div>

    </form>

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
        :disabled="isSubmitting"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-sky-500 hover:from-indigo-500 hover:to-sky-400 text-white font-bold text-xs shadow-md shadow-indigo-500/25 transition-all cursor-pointer disabled:opacity-50"
      >
        <RefreshCw v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" />
        <UserPlus v-else class="w-3.5 h-3.5" />
        <span>{{ isSubmitting ? 'Mendaftarkan...' : 'Daftarkan Pengguna' }}</span>
      </button>
    </template>
  </Dialog>
</template>
