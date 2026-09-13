<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import UserAvatar from '~/components/UserAvatar.vue'
import type { UserAccountItem, UserRoleType } from '~/types/auth'
import {
  Users,
  Search,
  Plus,
  ShieldCheck,
  ShieldAlert,
  User,
  KeyRound,
  Fingerprint,
  MoreVertical,
  Check,
  Copy,
  UserX,
  UserCheck,
  Sparkles,
  Filter
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth', 'admin'],
  layout: 'dashboard'
})

const { user, canManageUsers, isSuperadmin } = useAuth()

// Mock Directory
const userList = ref<UserAccountItem[]>([
  {
    id: 'usr_98a72b',
    name: 'Kukuh (Suamiku)',
    email: 'kukuh@banglipai.web.id',
    role: 'Superadmin',
    status: 'active',
    lastActive: 'Baru saja',
    authMethod: 'SSO Passkey'
  },
  {
    id: 'usr_54c81f',
    name: 'DevOps Administrator',
    email: 'admin@banglipai.web.id',
    role: 'Admin Account',
    status: 'active',
    lastActive: '5 menit lalu',
    authMethod: 'BFF Session'
  },
  {
    id: 'usr_23d90a',
    name: 'Ahmad Fauzi',
    email: 'fauzi@banglipai.web.id',
    role: 'User',
    status: 'active',
    lastActive: '1 jam lalu',
    authMethod: 'Password'
  },
  {
    id: 'usr_76e43c',
    name: 'Siti Nurhaliza',
    email: 'siti@banglipai.web.id',
    role: 'User',
    status: 'active',
    lastActive: '3 jam lalu',
    authMethod: 'SSO Passkey'
  },
  {
    id: 'usr_11b29d',
    name: 'Budi Santoso',
    email: 'budi@banglipai.web.id',
    role: 'User',
    status: 'suspended',
    lastActive: '3 hari lalu',
    authMethod: 'Password'
  }
])

const searchQuery = ref('')
const selectedRoleFilter = ref<string>('all')
const copiedEmail = ref<string | null>(null)

const filteredUsers = computed(() => {
  return userList.value.filter(u => {
    const matchQuery = !searchQuery.value || 
      u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
      u.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      u.id.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchRole = selectedRoleFilter.value === 'all' || u.role === selectedRoleFilter.value

    return matchQuery && matchRole
  })
})

function copyToClipboard(text: string) {
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(text)
    copiedEmail.value = text
    setTimeout(() => {
      copiedEmail.value = null
    }, 2000)
  }
}

function toggleUserStatus(targetUser: UserAccountItem) {
  if (targetUser.role === 'Superadmin') return
  targetUser.status = targetUser.status === 'active' ? 'suspended' : 'active'
}

function getAvatarGradient(role: UserRoleType): string {
  switch (role) {
    case 'Superadmin':
      return 'from-rose-500 to-pink-600 shadow-rose-500/20'
    case 'Admin Account':
      return 'from-sky-500 to-indigo-600 shadow-sky-500/20'
    default:
      return 'from-indigo-500 to-violet-600 shadow-indigo-500/20'
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
                {{ filteredUsers.length }} Akun
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
                @click="selectedRoleFilter = 'all'"
                class="px-3 py-1.5 rounded-xl transition-all font-medium cursor-pointer"
                :class="selectedRoleFilter === 'all' 
                  ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-bold' 
                  : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'"
              >
                Semua
              </button>
              <button
                type="button"
                @click="selectedRoleFilter = 'Superadmin'"
                class="px-3 py-1.5 rounded-xl transition-all font-medium cursor-pointer"
                :class="selectedRoleFilter === 'Superadmin' 
                  ? 'bg-rose-500 text-white shadow-xs font-bold' 
                  : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'"
              >
                Superadmin
              </button>
              <button
                type="button"
                @click="selectedRoleFilter = 'Admin Account'"
                class="px-3 py-1.5 rounded-xl transition-all font-medium cursor-pointer"
                :class="selectedRoleFilter === 'Admin Account' 
                  ? 'bg-sky-500 text-white shadow-xs font-bold' 
                  : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'"
              >
                Admin
              </button>
              <button
                type="button"
                @click="selectedRoleFilter = 'User'"
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
            :value="filteredUsers"
            responsiveLayout="scroll"
            class="p-datatable-modern text-xs"
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
                    :avatar="data.avatarUrl"
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
                      {{ data.id }}
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
            <Column header="Aksi" style="min-width: 120px" class="text-right">
              <template #body="{ data }">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    type="button"
                    class="px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-[11px] font-semibold text-slate-700 dark:text-slate-300 transition-all cursor-pointer hover:border-indigo-500/50"
                  >
                    Edit
                  </button>

                  <button
                    v-if="data.role !== 'Superadmin'"
                    type="button"
                    @click="toggleUserStatus(data)"
                    class="p-1.5 rounded-xl border transition-all cursor-pointer"
                    :class="data.status === 'active' 
                      ? 'border-rose-200 dark:border-rose-900/40 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50' 
                      : 'border-emerald-200 dark:border-emerald-900/40 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/50'"
                    :title="data.status === 'active' ? 'Suspend Akun' : 'Aktifkan Akun'"
                  >
                    <UserX v-if="data.status === 'active'" class="w-3.5 h-3.5" />
                    <UserCheck v-else class="w-3.5 h-3.5" />
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
        </div>

      </div>

    </main>
  </div>
</template>
