<script setup lang="ts">
import type { UserRecord } from '~/composables/useUsers'
import {
  UserX,
  UserCheck,
  AlertTriangle,
  RefreshCw,
  ShieldAlert,
  ShieldCheck,
  AlertCircle
} from 'lucide-vue-next'

const props = defineProps<{
  modelValue: boolean
  user: UserRecord | null
  currentUserId?: string
  isSuperadmin: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'statusChanged'): void
}>()

const { changeStatus } = useUsers()
const toast = useToast()

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const isSubmitting = ref(false)
const errorMessage = ref<string | null>(null)

// Evaluasi status saat ini
const isCurrentlySuspended = computed(() => {
  return props.user?.status === 'suspended' || props.user?.isSuspended === true
})

// Proteksi keamanan: tidak bisa suspend diri sendiri
const isSelf = computed(() => {
  return Boolean(props.user && props.currentUserId && String(props.user.id) === String(props.currentUserId))
})

// Proteksi keamanan: Superadmin tidak dapat disuspen
const isTargetSuperadmin = computed(() => {
  return props.user?.role === 'Superadmin'
})

// Proteksi keamanan: Admin hanya boleh mengelola Regular User
const isTargetAdmin = computed(() => {
  return props.user?.role === 'Admin Account'
})

const canToggle = computed(() => {
  if (isSelf.value) return false
  if (isTargetSuperadmin.value) return false
  if (isTargetAdmin.value && !props.isSuperadmin) return false
  return true
})

async function onConfirmToggle() {
  if (!props.user || !canToggle.value) return

  isSubmitting.value = true
  errorMessage.value = null

  try {
    const desiredStatus = isCurrentlySuspended.value ? 'active' : 'suspended'
    const res = await changeStatus(props.user.id, desiredStatus)

    toast.add({
      severity: 'success',
      summary: isCurrentlySuspended.value ? 'Akun Diaktifkan' : 'Akun Ditangguhkan',
      detail: res.message || (isCurrentlySuspended.value ? 'Akun pengguna berhasil diaktifkan kembali.' : 'Akun pengguna berhasil ditangguhkan.'),
      life: 4000
    })

    emit('statusChanged')
    visible.value = false
  } catch (err: unknown) {
    const e = err as Error
    errorMessage.value = e.message || 'Gagal mengubah status akun pengguna.'
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
    class="w-full max-w-md mx-4 !rounded-3xl overflow-hidden shadow-2xl !backdrop-blur-xl"
    :class="isCurrentlySuspended 
      ? '!border !border-emerald-200 dark:!border-emerald-950/80 !bg-white/95 dark:!bg-slate-950/95' 
      : '!border !border-amber-200 dark:!border-amber-950/80 !bg-white/95 dark:!bg-slate-950/95'"
    :pt="{
      header: { 
        class: isCurrentlySuspended
          ? '!p-6 !border-b !border-emerald-100 dark:!border-emerald-950/50 !bg-gradient-to-b !from-emerald-50/50 dark:!from-emerald-950/20 !to-transparent'
          : '!p-6 !border-b !border-amber-100 dark:!border-amber-950/50 !bg-gradient-to-b !from-amber-50/50 dark:!from-amber-950/20 !to-transparent'
      },
      content: { class: '!p-6 space-y-4' },
      footer: { class: '!p-5 !border-t !border-slate-100 dark:!border-slate-800/80 !bg-slate-50/50 dark:!bg-slate-900/40 flex justify-end gap-3' }
    }"
  >
    <!-- Modal Header -->
    <template #header>
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-2xl flex items-center justify-center border"
          :class="isCurrentlySuspended
            ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20'
            : 'bg-amber-500/10 text-amber-500 border-amber-500/20'"
        >
          <UserCheck v-if="isCurrentlySuspended" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
          <UserX v-else class="w-5 h-5 text-amber-600 dark:text-amber-400" />
        </div>
        <div>
          <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">
            {{ isCurrentlySuspended ? 'Aktifkan Kembali Akun?' : 'Tangguhkan Akun Pengguna?' }}
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">
            {{ isCurrentlySuspended ? 'Memulihkan hak akses login SSO ekosistem' : 'Membekukan sementara sesi dan akses login' }}
          </p>
        </div>
      </div>
    </template>

    <div class="space-y-4 text-xs">
      
      <!-- Error Alert -->
      <div v-if="errorMessage" class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-300 flex items-start gap-2">
        <AlertCircle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
        <p class="text-xs">{{ errorMessage }}</p>
      </div>

      <!-- Self Suspension Barrier Alert -->
      <div v-if="isSelf" class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-300 space-y-1">
        <div class="font-bold flex items-center gap-1.5">
          <ShieldAlert class="w-4 h-4 text-rose-500" />
          <span>Tindakan Tidak Diizinkan</span>
        </div>
        <p class="text-[11px]">
          Anda tidak dapat menangguhkan akun Anda sendiri. Kebijakan ini mencegah administrator terkunci dari sistem secara tidak sengaja.
        </p>
      </div>

      <!-- Superadmin Suspension Barrier Alert -->
      <div v-else-if="isTargetSuperadmin" class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-300 space-y-1">
        <div class="font-bold flex items-center gap-1.5">
          <ShieldAlert class="w-4 h-4 text-rose-500" />
          <span>Perlindungan Akun Superadmin</span>
        </div>
        <p class="text-[11px]">
          Akun dengan level Superadmin dilindungi oleh Kebijakan Keamanan Sistem dan tidak dapat ditangguhkan demi menjaga kelangsungan operasional cluster.
        </p>
      </div>

      <!-- Admin Suspending Fellow Admin Alert -->
      <div v-else-if="!isSuperadmin && isTargetAdmin" class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/60 text-amber-800 dark:text-amber-300 space-y-1">
        <div class="font-bold flex items-center gap-1.5">
          <ShieldAlert class="w-4 h-4 text-amber-500" />
          <span>Pembatasan Hak Akses (RBAC)</span>
        </div>
        <p class="text-[11px]">
          Hanya Superadmin yang berwenang menangguhkan atau mengaktifkan sesama akun Admin.
        </p>
      </div>

      <!-- Target User Information Card -->
      <div v-if="user" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center gap-3.5">
        <UserAvatar
          :name="user.name"
          :email="user.email"
          :avatar="user.avatar"
          :role="user.role"
          size="md"
        />
        <div class="min-w-0 flex-1">
          <p class="font-bold text-slate-900 dark:text-white text-xs truncate">
            {{ user.name }}
          </p>
          <p class="font-mono text-[10px] text-slate-400 truncate mt-0.5">
            {{ user.email }}
          </p>
          <div class="mt-1.5 flex items-center gap-2">
            <Tag
              :value="user.role"
              :severity="user.role === 'Superadmin' ? 'danger' : (user.role === 'Admin Account' ? 'info' : 'secondary')"
              class="!text-[9px] !font-bold"
            />
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono"
              :class="isCurrentlySuspended 
                ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' 
                : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'"
            >
              {{ isCurrentlySuspended ? 'Saat ini: Suspended' : 'Saat ini: Aktif' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Consequence Explanations -->
      <div v-if="canToggle">
        <div
          v-if="!isCurrentlySuspended"
          class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-900 dark:text-amber-200 space-y-1.5"
        >
          <div class="font-bold flex items-center gap-1.5 text-xs text-amber-700 dark:text-amber-300">
            <AlertTriangle class="w-4 h-4 text-amber-500 shrink-0" />
            <span>Konsekuensi Penangguhan Akun</span>
          </div>
          <ul class="text-[11px] list-disc list-inside space-y-1 text-slate-600 dark:text-slate-300 pl-1">
            <li>Pengguna tidak dapat login ke portal SSO maupun aplikasi ekosistem banglipai.web.id.</li>
            <li>Sesi login yang sedang aktif di Logto akan otomatis dicabut.</li>
            <li>Data riwayat akun tetap aman dan dapat diaktifkan kembali kapan saja.</li>
          </ul>
        </div>

        <div
          v-else
          class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-900 dark:text-emerald-200 space-y-1.5"
        >
          <div class="font-bold flex items-center gap-1.5 text-xs text-emerald-700 dark:text-emerald-300">
            <ShieldCheck class="w-4 h-4 text-emerald-500 shrink-0" />
            <span>Pemulihan Hak Akses Akun</span>
          </div>
          <p class="text-[11px] text-slate-600 dark:text-slate-300">
            Pengguna akan dapat langsung melakukan login SSO kembali dan mengakses seluruh hak akses sesuai role yang diberikan.
          </p>
        </div>
      </div>

    </div>

    <!-- Modal Footer -->
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
        @click="onConfirmToggle"
        :disabled="!canToggle || isSubmitting"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-xs shadow-md transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed text-white"
        :class="isCurrentlySuspended
          ? 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/25'
          : 'bg-amber-600 hover:bg-amber-500 shadow-amber-600/25'"
      >
        <RefreshCw v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" />
        <UserCheck v-else-if="isCurrentlySuspended" class="w-3.5 h-3.5" />
        <UserX v-else class="w-3.5 h-3.5" />
        <span>
          {{ isSubmitting 
            ? 'Memproses...' 
            : (isCurrentlySuspended ? 'Ya, Aktifkan Akun' : 'Ya, Tangguhkan Akun') 
          }}
        </span>
      </button>
    </template>
  </Dialog>
</template>
