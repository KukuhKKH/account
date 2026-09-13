<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import { useUsers, type UserRecord } from '~/composables/useUsers'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import UserAvatar from '~/components/UserAvatar.vue'
import CreateUserModal from '~/components/modals/CreateUserModal.vue'
import EditUserModal from '~/components/modals/EditUserModal.vue'
import DeleteUserDialog from '~/components/modals/DeleteUserDialog.vue'
import SuspendUserDialog from '~/components/modals/SuspendUserDialog.vue'
import ResetPasswordModal from '~/components/modals/ResetPasswordModal.vue'
import type { UserRoleType } from '~/types/auth'
import {
  Search,
  Plus,
  KeyRound,
  Fingerprint,
  Check,
  Copy,
  UserX,
  UserCheck,
  Sparkles,
  RefreshCw,
  Trash2,
  Edit3,
  ShieldCheck,
  Lock,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth', 'admin'],
  layout: 'dashboard'
})

const { user: currentUser, canManageUsers, isSuperadmin } = useAuth()
const {
  users,
  meta,
  isLoading,
  error,
  fetchUsers,
  changeStatus
} = useUsers()

const toast = useToast()

// Filter & Search State
const searchQuery = ref('')
const selectedRoleFilter = ref<string>('all')
const copiedEmail = ref<string | null>(null)

// Modals State
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteDialog = ref(false)
const showSuspendDialog = ref(false)
const showResetPasswordModal = ref(false)
const selectedUserForEdit = ref<UserRecord | null>(null)
const selectedUserForDelete = ref<UserRecord | null>(null)
const selectedUserForSuspend = ref<UserRecord | null>(null)
const selectedUserForResetPassword = ref<UserRecord | null>(null)

// Action loading indicators per-user
const togglingUserId = ref<string | null>(null)

// Initial fetch on mount
onMounted(() => {
  fetchUsers()
})

// Debounced search watcher
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, (newVal) => {
  if (searchDebounceTimer) clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    fetchUsers({
      search: newVal,
      role: selectedRoleFilter.value,
      page: 1
    })
  }, 350)
})

function onFilterRole(role: string) {
  selectedRoleFilter.value = role
  fetchUsers({
    search: searchQuery.value,
    role: role,
    page: 1
  })
}

function onPageChange(newPage: number) {
  if (newPage < 1 || newPage > meta.value.lastPage) return
  fetchUsers({
    search: searchQuery.value,
    role: selectedRoleFilter.value,
    page: newPage
  })
}

function copyToClipboard(text: string) {
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(text)
    copiedEmail.value = text
    setTimeout(() => {
      copiedEmail.value = null
    }, 2000)
  }
}

function openEditModal(target: UserRecord) {
  selectedUserForEdit.value = target
  showEditModal.value = true
}

function openDeleteDialog(target: UserRecord) {
  selectedUserForDelete.value = target
  showDeleteDialog.value = true
}

function openSuspendDialog(target: UserRecord) {
  selectedUserForSuspend.value = target
  showSuspendDialog.value = true
}

function openResetPasswordModal(target: UserRecord) {
  if (currentUser.value && String(target.id) === String(currentUser.value.id)) {
    toast.add({
      severity: 'warn',
      summary: 'Akun Sendiri',
      detail: 'Untuk mengubah kata sandi akun sendiri, silakan buka menu Profil Pengguna & Kunci Keamanan dan masukkan kata sandi lama.',
      life: 5000
    })
    return
  }

  selectedUserForResetPassword.value = target
  showResetPasswordModal.value = true
}

async function handleToggleStatus(target: UserRecord) {
  // Prevent self-suspension or suspending Superadmin if actor is not authorized
  if (currentUser.value && String(target.id) === String(currentUser.value.id)) {
    toast.add({
      severity: 'warn',
      summary: 'Tindakan Dicegah',
      detail: 'Anda tidak dapat menangguhkan akun Anda sendiri.',
      life: 3500
    })
    return
  }

  if (target.role === 'Superadmin') {
    toast.add({
      severity: 'error',
      summary: 'Kebijakan Keamanan',
      detail: 'Akun Superadmin tidak dapat ditangguhkan demi kestabilan cluster.',
      life: 4000
    })
    return
  }

  togglingUserId.value = target.id

  try {
    const desiredStatus = target.status === 'active' ? 'suspended' : 'active'
    const res = await changeStatus(target.id, desiredStatus)
    await fetchUsers()
    toast.add({
      severity: 'success',
      summary: 'Status Diperbarui',
      detail: res.message || 'Status akun berhasil diperbarui.',
      life: 3500
    })
  } catch (err: unknown) {
    const e = err as Error
    toast.add({
      severity: 'error',
      summary: 'Gagal Mengubah Status',
      detail: e.message || 'Terjadi kesalahan saat memproses status akun.',
      life: 4000
    })
  } finally {
    togglingUserId.value = null
  }
}
</script>

<template>
  <div class="flex-1 flex flex-col min-w-0">
    <DashboardHeader
      title="Manajemen Pengguna & Kebijakan Hak Akses"
      subtitle="Kelola direktori identitas SSO, role RBAC, dan audit status akun ekosistem banglipai.web.id"
    />

    <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 overflow-y-auto">
      
      <!-- Top Action & Search Bar -->
      <div class="glass-panel rounded-3xl p-5 sm:p-6 space-y-5">
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">
                Direktori Akun Pengguna
              </h2>
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-sky-400 border border-indigo-200 dark:border-indigo-800">
                {{ meta.total }} Akun
              </span>
              <span v-if="isLoading" class="flex items-center gap-1 text-[11px] font-medium text-slate-400">
                <RefreshCw class="w-3 h-3 animate-spin text-indigo-500" />
                <span>Menyinkronkan...</span>
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Otorisasi tersinkronisasi langsung dengan Logto Management Engine & Central DB
            </p>
          </div>

          <!-- Controls: Search, Filter, Add User -->
          <div class="flex flex-wrap items-center gap-3">
            
            <!-- Search Box -->
            <div class="relative min-w-[240px] flex-1 sm:flex-initial">
              <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
              <InputText
                v-model="searchQuery"
                placeholder="Cari nama, email, ID..."
                class="w-full !pl-9.5 !pr-4 !py-2.5 !rounded-2xl !text-xs !bg-slate-50/80 dark:!bg-slate-900/80 !border-slate-200 dark:!border-slate-800 focus:!border-indigo-500 focus:!ring-2 focus:!ring-indigo-500/20 transition-all"
              />
            </div>

            <!-- Role Filter Pills -->
            <div class="flex items-center p-1 rounded-2xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs">
              <button
                type="button"
                @click="onFilterRole('all')"
                class="px-3 py-1.5 rounded-xl transition-all font-medium cursor-pointer"
                :class="selectedRoleFilter === 'all' 
                  ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-bold' 
                  : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'"
              >
                Semua
              </button>
              <button
                type="button"
                @click="onFilterRole('Superadmin')"
                class="px-3 py-1.5 rounded-xl transition-all font-medium cursor-pointer"
                :class="selectedRoleFilter === 'Superadmin' 
                  ? 'bg-rose-500 text-white shadow-xs font-bold' 
                  : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'"
              >
                Superadmin
              </button>
              <button
                type="button"
                @click="onFilterRole('Admin Account')"
                class="px-3 py-1.5 rounded-xl transition-all font-medium cursor-pointer"
                :class="selectedRoleFilter === 'Admin Account' 
                  ? 'bg-sky-500 text-white shadow-xs font-bold' 
                  : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'"
              >
                Admin
              </button>
              <button
                type="button"
                @click="onFilterRole('User')"
                class="px-3 py-1.5 rounded-xl transition-all font-medium cursor-pointer"
                :class="selectedRoleFilter === 'User' 
                  ? 'bg-indigo-500 text-white shadow-xs font-bold' 
                  : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'"
              >
                Member
              </button>
            </div>

            <!-- Add User Button -->
            <button
              type="button"
              @click="showCreateModal = true"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-sky-500 hover:from-indigo-500 hover:to-sky-400 text-white font-bold text-xs shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all cursor-pointer hover:-translate-y-0.5"
            >
              <Plus class="w-4 h-4" />
              <span>Tambah User</span>
            </button>

          </div>

        </div>

        <!-- ============================================== -->
        <!-- ULTRA-MODERN PRIMEVUE DATA TABLE -->
        <!-- ============================================== -->
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 overflow-hidden bg-white/40 dark:bg-slate-950/40 backdrop-blur-md">
          <DataTable
            :value="users"
            responsiveLayout="scroll"
            class="p-datatable-modern text-xs"
            :loading="isLoading && users.length === 0"
            :pt="{
              table: { class: '!w-full !border-collapse' },
              thead: { class: '!bg-slate-100/70 dark:!bg-slate-900/80 !border-b !border-slate-200/80 dark:!border-slate-800/80' },
              headerRow: { class: '!text-slate-500 dark:!text-slate-400 !font-mono !text-[11px] !uppercase !tracking-wider' },
              bodyRow: { class: '!border-b !border-slate-100 dark:!border-slate-800/50 hover:!bg-indigo-50/40 dark:hover:!bg-indigo-950/20 !transition-colors' }
            }"
          >
            <!-- Column 1: User Identity & Avatar -->
            <Column header="Pengguna" style="min-width: 220px">
              <template #body="{ data }">
                <div class="flex items-center gap-3 py-1">
                  <UserAvatar
                    :name="data.name"
                    :email="data.email"
                    :avatar="data.avatar"
                    :role="data.role"
                    size="md"
                    class="shadow-md"
                  />
                  <div class="min-w-0">
                    <p class="font-extrabold text-slate-900 dark:text-white truncate text-xs flex items-center gap-1.5">
                      <span>{{ data.name }}</span>
                      <Sparkles v-if="data.role === 'Superadmin'" class="w-3.5 h-3.5 text-amber-500" />
                    </p>
                    <p class="text-[10px] text-slate-400 font-mono truncate mt-0.5">
                      ID: {{ data.id }} {{ data.logtoId ? `• ${data.logtoId}` : '' }}
                    </p>
                  </div>
                </div>
              </template>
            </Column>

            <!-- Column 2: SSO Email & Quick Copy -->
            <Column header="Email SSO" style="min-width: 220px">
              <template #body="{ data }">
                <div class="flex items-center gap-2 group">
                  <span class="font-mono text-xs text-slate-700 dark:text-slate-300">{{ data.email }}</span>
                  <button
                    type="button"
                    @click="copyToClipboard(data.email)"
                    class="p-1 rounded-lg text-slate-400 hover:text-indigo-600 dark:hover:text-sky-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all opacity-0 group-hover:opacity-100 cursor-pointer"
                    :title="copiedEmail === data.email ? 'Tersalin!' : 'Salin Email'"
                  >
                    <Check v-if="copiedEmail === data.email" class="w-3.5 h-3.5 text-emerald-500" />
                    <Copy v-else class="w-3.5 h-3.5" />
                  </button>
                </div>
              </template>
            </Column>

            <!-- Column 3: Role & Privilege -->
            <Column header="Role Akses" style="min-width: 140px">
              <template #body="{ data }">
                <div class="inline-flex items-center gap-1.5">
                  <Tag
                    :value="data.role"
                    :severity="data.role === 'Superadmin' ? 'danger' : (data.role === 'Admin Account' ? 'info' : 'secondary')"
                    class="!text-[10px] !font-bold !px-2.5 !py-1 !rounded-xl"
                  />
                </div>
              </template>
            </Column>

            <!-- Column 4: Auth Method -->
            <Column header="Metode Auth" style="min-width: 130px">
              <template #body="{ data }">
                <div class="inline-flex items-center gap-1.5 text-[11px] font-mono text-slate-600 dark:text-slate-400">
                  <Fingerprint v-if="data.authMethod.includes('Passkey')" class="w-3.5 h-3.5 text-indigo-500" />
                  <KeyRound v-else class="w-3.5 h-3.5 text-slate-400" />
                  <span>{{ data.authMethod }}</span>
                </div>
              </template>
            </Column>

            <!-- Column 5: Status with Pulsating Glow -->
            <Column header="Status" style="min-width: 110px">
              <template #body="{ data }">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[11px] font-bold"
                  :class="data.status === 'active' 
                    ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' 
                    : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'"
                >
                  <span class="relative flex h-2 w-2">
                    <span
                      v-if="data.status === 'active'"
                      class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                    ></span>
                    <span
                      class="relative inline-flex rounded-full h-2 w-2"
                      :class="data.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500'"
                    ></span>
                  </span>
                  <span>{{ data.status === 'active' ? 'Aktif' : 'Suspended' }}</span>
                </div>
              </template>
            </Column>

            <!-- Column 6: Last Active -->
            <Column header="Aktivitas" style="min-width: 110px">
              <template #body="{ data }">
                <span class="text-slate-400 font-mono text-[11px]">{{ data.lastActive }}</span>
              </template>
            </Column>

            <!-- Column 7: Action Controls -->
            <Column header="Aksi" style="min-width: 140px" class="text-right">
              <template #body="{ data }">
                <div class="flex items-center justify-end gap-1.5">
                  
                  <!-- Edit Button -->
                  <button
                    type="button"
                    @click="openEditModal(data)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300 transition-all cursor-pointer hover:border-indigo-500/50"
                  >
                    <Edit3 class="w-3 h-3 text-slate-400" />
                    <span>Edit</span>
                  </button>

                  <!-- Reset Password Button -->
                  <button
                    v-if="(isSuperadmin || data.role === 'User') && (!currentUser || String(data.id) !== String(currentUser.id))"
                    type="button"
                    @click="openResetPasswordModal(data)"
                    class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-indigo-600 dark:hover:text-sky-400 hover:border-indigo-200 dark:hover:border-indigo-900/40 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/40 transition-all cursor-pointer"
                    title="Reset Kata Sandi"
                  >
                    <KeyRound class="w-3.5 h-3.5" />
                  </button>

                  <!-- Toggle Suspend Button -->
                  <button
                    v-if="data.role !== 'Superadmin' && (isSuperadmin || data.role === 'User') && (!currentUser || String(data.id) !== String(currentUser.id))"
                    type="button"
                    @click="openSuspendDialog(data)"
                    class="p-1.5 rounded-xl border transition-all cursor-pointer disabled:opacity-50"
                    :class="data.status === 'active' 
                      ? 'border-rose-200 dark:border-rose-900/40 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50' 
                      : 'border-emerald-200 dark:border-emerald-900/40 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/50'"
                    :title="data.status === 'active' ? 'Tangguhkan Akun' : 'Aktifkan Akun'"
                  >
                    <UserX v-if="data.status === 'active'" class="w-3.5 h-3.5" />
                    <UserCheck v-else class="w-3.5 h-3.5" />
                  </button>

                  <!-- Delete Button -->
                  <button
                    v-if="isSuperadmin || data.role === 'User'"
                    type="button"
                    @click="openDeleteDialog(data)"
                    class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-400 hover:text-rose-600 hover:border-rose-200 dark:hover:border-rose-900/40 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all cursor-pointer"
                    title="Hapus Pengguna"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>

                </div>
              </template>
            </Column>

            <!-- Empty State Template -->
            <template #empty>
              <div class="py-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 mx-auto flex items-center justify-center">
                  <Search class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Tidak ada pengguna yang cocok</p>
                  <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau filter peran.</p>
                </div>
              </div>
            </template>
          </DataTable>

          <!-- Modern Bottom Pagination Bar -->
          <div v-if="meta.lastPage > 1" class="p-4 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/40 flex items-center justify-between text-xs">
            <p class="text-slate-500 dark:text-slate-400">
              Menampilkan halaman <span class="font-bold text-slate-900 dark:text-white">{{ meta.currentPage }}</span> dari <span class="font-bold text-slate-900 dark:text-white">{{ meta.lastPage }}</span> (Total {{ meta.total }} pengguna)
            </p>

            <div class="flex items-center gap-1.5">
              <button
                type="button"
                @click="onPageChange(meta.currentPage - 1)"
                :disabled="meta.currentPage <= 1"
                class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer"
              >
                <ChevronLeft class="w-4 h-4" />
              </button>

              <span class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-800 font-mono font-bold text-slate-800 dark:text-white">
                {{ meta.currentPage }}
              </span>

              <button
                type="button"
                @click="onPageChange(meta.currentPage + 1)"
                :disabled="meta.currentPage >= meta.lastPage"
                class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer"
              >
                <ChevronRight class="w-4 h-4" />
              </button>
            </div>
          </div>

        </div>

      </div>

    </main>

    <!-- Modals Section -->
    <CreateUserModal
      v-model="showCreateModal"
      :isSuperadmin="isSuperadmin"
      @created="fetchUsers"
    />

    <EditUserModal
      v-model="showEditModal"
      :user="selectedUserForEdit"
      :isSuperadmin="isSuperadmin"
      @updated="fetchUsers"
    />

    <DeleteUserDialog
      v-model="showDeleteDialog"
      :user="selectedUserForDelete"
      :currentUserId="currentUser?.id"
      :isSuperadmin="isSuperadmin"
      @deleted="fetchUsers"
    />

    <SuspendUserDialog
      v-model="showSuspendDialog"
      :user="selectedUserForSuspend"
      :currentUserId="currentUser?.id"
      :isSuperadmin="isSuperadmin"
      @statusChanged="fetchUsers"
    />

    <ResetPasswordModal
      v-model="showResetPasswordModal"
      :user="selectedUserForResetPassword"
      :currentUserId="currentUser?.id"
      :isSuperadmin="isSuperadmin"
      @passwordReset="fetchUsers"
    />

  </div>
</template>
