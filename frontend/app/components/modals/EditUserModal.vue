<script setup lang="ts">
import type { UserRoleType } from '~/types/auth'
import type { UserRecord } from '~/composables/useUsers'
import {
  UserCheck,
  User,
  Mail,
  Phone,
  MapPin,
  ShieldCheck,
  ShieldAlert,
  Sparkles,
  RefreshCw,
  AlertCircle
} from 'lucide-vue-next'

const props = defineProps<{
  modelValue: boolean
  user: UserRecord | null
  isSuperadmin: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'updated'): void
}>()

const { updateUser } = useUsers()
const toast = useToast()

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const isSubmitting = ref(false)
const errorMessage = ref<string | null>(null)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  address: '',
  role: 'User' as UserRoleType
})

watch(() => props.user, (u) => {
  if (u) {
    form.name = u.name
    form.email = u.email
    form.phone = u.phone || ''
    form.address = u.address || ''
    form.role = u.role
    errorMessage.value = null
  }
}, { immediate: true })

async function onSubmit() {
  if (!props.user) return

  if (!form.name.trim() || !form.email.trim()) {
    errorMessage.value = 'Nama lengkap dan alamat email tidak boleh kosong.'
    return
  }

  isSubmitting.value = true
  errorMessage.value = null

  try {
    const res = await updateUser(props.user.id, {
      name: form.name.trim(),
      email: form.email.trim(),
      role: form.role,
      phone: form.phone.trim() || undefined,
      address: form.address.trim() || undefined
    })

    toast.add({
      severity: 'success',
      summary: 'Data Diperbarui',
      detail: res.message || 'Profil dan peran pengguna berhasil disimpan.',
      life: 4000
    })

    emit('updated')
    visible.value = false
  } catch (err: unknown) {
    const e = err as Error
    errorMessage.value = e.message || 'Gagal memperbarui data pengguna.'
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
        <UserAvatar
          v-if="user"
          :name="user.name"
          :email="user.email"
          :avatar="user.avatar"
          :role="user.role"
          size="lg"
          class="shadow-md"
        />
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
            <span>Edit Profil & Akses Pengguna</span>
            <Tag
              v-if="user"
              :value="user.role"
              :severity="user.role === 'Superadmin' ? 'danger' : (user.role === 'Admin Account' ? 'info' : 'secondary')"
              class="!text-[10px] !font-bold"
            />
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono">
            ID: {{ user?.id }} {{ user?.logtoId ? `• Logto: ${user.logtoId}` : '' }}
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
            class="w-full !rounded-2xl !text-xs !bg-slate-50/70 dark:!bg-slate-900/70 !border-slate-200 dark:!border-slate-800 focus:!border-indigo-500 !py-2.5"
          />
        </div>

      </div>

      <!-- Role Selection Section -->
      <div class="space-y-2 pt-1">
        <label class="font-bold text-slate-700 dark:text-slate-300 flex items-center justify-between">
          <span class="flex items-center gap-1.5">
            <ShieldCheck class="w-3.5 h-3.5 text-indigo-500" />
            <span>Pembaruan Peran Hak Akses (RBAC)</span>
          </span>
          <span class="text-[10px] text-slate-400 font-normal">Policy Protected</span>
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
          
          <!-- Role: User -->
          <div
            @click="form.role = 'User'"
            class="p-3.5 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between space-y-2"
            :class="form.role === 'User'
              ? 'border-indigo-500 bg-indigo-500/10 shadow-md shadow-indigo-500/10 ring-2 ring-indigo-500/30'
              : 'border-slate-200 dark:border-slate-800 hover:border-indigo-300 bg-slate-50/50 dark:bg-slate-900/50'"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">User</span>
              <span class="w-2 h-2 rounded-full" :class="form.role === 'User' ? 'bg-indigo-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
            </div>
            <span class="text-[9px] font-mono text-indigo-600 dark:text-sky-400 font-semibold">Standard Member</span>
          </div>

          <!-- Role: Admin Account -->
          <div
            @click="isSuperadmin && (form.role = 'Admin Account')"
            class="p-3.5 rounded-2xl border transition-all flex flex-col justify-between space-y-2"
            :class="[
              !isSuperadmin ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-900/30 border-dashed' : 'cursor-pointer',
              form.role === 'Admin Account'
                ? 'border-sky-500 bg-sky-500/10 shadow-md shadow-sky-500/10 ring-2 ring-sky-500/30'
                : 'border-slate-200 dark:border-slate-800 hover:border-sky-300 bg-slate-50/50 dark:bg-slate-900/50'
            ]"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center gap-1">
                <span>Admin</span>
                <ShieldCheck class="w-3 h-3 text-sky-500" />
              </span>
              <span class="w-2 h-2 rounded-full" :class="form.role === 'Admin Account' ? 'bg-sky-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
            </div>
            <span class="text-[9px] font-mono text-sky-600 dark:text-sky-400 font-semibold">
              {{ isSuperadmin ? 'User Management' : 'Superadmin Only' }}
            </span>
          </div>

          <!-- Role: Superadmin -->
          <div
            @click="isSuperadmin && (form.role = 'Superadmin')"
            class="p-3.5 rounded-2xl border transition-all flex flex-col justify-between space-y-2"
            :class="[
              !isSuperadmin ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-900/30 border-dashed' : 'cursor-pointer',
              form.role === 'Superadmin'
                ? 'border-rose-500 bg-rose-500/10 shadow-md shadow-rose-500/10 ring-2 ring-rose-500/30'
                : 'border-slate-200 dark:border-slate-800 hover:border-rose-300 bg-slate-50/50 dark:bg-slate-900/50'
            ]"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center gap-1">
                <span>Superadmin</span>
                <Sparkles class="w-3 h-3 text-rose-500" />
              </span>
              <span class="w-2 h-2 rounded-full" :class="form.role === 'Superadmin' ? 'bg-rose-500' : 'bg-slate-300 dark:bg-slate-700'"></span>
            </div>
            <span class="text-[9px] font-mono text-rose-600 dark:text-rose-400 font-semibold">
              {{ isSuperadmin ? 'Full Power' : 'Superadmin Only' }}
            </span>
          </div>

        </div>
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
        <UserCheck v-else class="w-3.5 h-3.5" />
        <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
      </button>
    </template>
  </Dialog>
</template>
