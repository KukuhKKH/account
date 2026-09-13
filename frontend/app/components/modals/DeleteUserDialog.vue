<script setup lang="ts">
import type { UserRecord } from '~/composables/useUsers'
import {
  AlertTriangle,
  Trash2,
  RefreshCw,
  ShieldAlert,
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
  (e: 'deleted'): void
}>()

const { deleteUser } = useUsers()
const toast = useToast()

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const isSubmitting = ref(false)
const errorMessage = ref<string | null>(null)

const isSelf = computed(() => {
  return props.user && props.currentUserId && String(props.user.id) === String(props.currentUserId)
})

const isTargetSuperadmin = computed(() => {
  return props.user && props.user.role === 'Superadmin'
})

const canDelete = computed(() => {
  if (isSelf.value) return false
  if (isTargetSuperadmin.value && !props.isSuperadmin) return false
  return true
})

async function onConfirmDelete() {
  if (!props.user || !canDelete.value) return

  isSubmitting.value = true
  errorMessage.value = null

  try {
    const res = await deleteUser(props.user.id)

    toast.add({
      severity: 'success',
      summary: 'Pengguna Dihapus',
      detail: res.message || 'Akun berhasil dihapus secara permanen dari sistem.',
      life: 4000
    })

    emit('deleted')
    visible.value = false
  } catch (err: unknown) {
    const e = err as Error
    errorMessage.value = e.message || 'Gagal menghapus pengguna.'
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
    class="w-full max-w-md mx-4 !rounded-3xl overflow-hidden !border !border-rose-200 dark:!border-rose-950/80 !bg-white/95 dark:!bg-slate-950/95 !backdrop-blur-xl shadow-2xl"
    :pt="{
      header: { class: '!p-6 !border-b !border-rose-100 dark:!border-rose-950/50 !bg-gradient-to-b !from-rose-50/50 dark:!from-rose-950/20 !to-transparent' },
      content: { class: '!p-6 space-y-4' },
      footer: { class: '!p-5 !border-t !border-slate-100 dark:!border-slate-800/80 !bg-slate-50/50 dark:!bg-slate-900/40 flex justify-end gap-3' }
    }"
  >
    <template #header>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center border border-rose-500/20">
          <AlertTriangle class="w-5 h-5 text-rose-600 dark:text-rose-400" />
        </div>
        <div>
          <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">
            Konfirmasi Penghapusan Akun
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">
            Tindakan destruktif tidak dapat dibatalkan
          </p>
        </div>
      </div>
    </template>

    <div class="space-y-4 text-xs">
      
      <!-- Error Message -->
      <div v-if="errorMessage" class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-300 flex items-start gap-2">
        <AlertCircle class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" />
        <p class="text-xs">{{ errorMessage }}</p>
      </div>

      <!-- Self Deletion Alert -->
      <div v-if="isSelf" class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900/60 text-amber-800 dark:text-amber-300 space-y-1">
        <div class="font-bold flex items-center gap-1.5">
          <ShieldAlert class="w-4 h-4 text-amber-500" />
          <span>Kebijakan Anti-Self-Harm Aktif</span>
        </div>
        <p class="text-[11px]">
          Anda tidak dapat menghapus akun Anda sendiri dari portal. Tindakan ini dicegah oleh Kebijakan Keamanan Sistem.
        </p>
      </div>

      <!-- Target Card Preview -->
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
          <div class="mt-1">
            <Tag
              :value="user.role"
              :severity="user.role === 'Superadmin' ? 'danger' : (user.role === 'Admin Account' ? 'info' : 'secondary')"
              class="!text-[9px] !font-bold"
            />
          </div>
        </div>
      </div>

      <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-[11px]">
        Apakah Anda yakin ingin menghapus akun ini secara permanen? Seluruh relasi data, otorisasi SSO OIDC di Logto, dan riwayat akses lokal akan dihapus.
      </p>

    </div>

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
        @click="onConfirmDelete"
        :disabled="!canDelete || isSubmitting"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-md shadow-rose-600/25 transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
      >
        <RefreshCw v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" />
        <Trash2 v-else class="w-3.5 h-3.5" />
        <span>{{ isSubmitting ? 'Menghapus...' : 'Hapus Permanen' }}</span>
      </button>
    </template>
  </Dialog>
</template>
