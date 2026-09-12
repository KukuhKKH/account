<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useClusterStore } from '~/stores/cluster'
import DashboardSidebar from '~/components/dashboard/DashboardSidebar.vue'
import DashboardHeader from '~/components/dashboard/DashboardHeader.vue'
import type { UserAccountItem } from '~/types/auth'
import {
  ShieldCheck,
  Users,
  Server,
  Activity,
  KeyRound,
  FileText,
  Lock,
  Plus,
  Search,
  CheckCircle2,
  AlertTriangle,
  Bot,
  UserCheck,
  RefreshCw,
  Eye,
  EyeOff,
  Zap,
  Radio,
  Sliders,
  Terminal
} from 'lucide-vue-next'

definePageMeta({
  middleware: ['auth'],
  layout: 'dashboard'
})

const authStore = useAuthStore()
const clusterStore = useClusterStore()

const activeTab = ref('overview')

// Dynamic Header Title
const currentTitle = computed(() => {
  switch (activeTab.value) {
    case 'users': return 'Manajemen Pengguna & Hak Akses'
    case 'cluster': return 'Klaster Node & Subnet Terproteksi'
    case 'audit': return 'Audit Keamanan & Log Aktivitas'
    case 'ai_ops': return 'Karina AI Ops Intelligence Center'
    case 'profile': return 'Profil & Pengaturan Keamanan Pribadi'
    default: return 'Control Center & Ringkasan Sistem'
  }
})

// Mock Users Directory for Superadmin & Admin Account
const userList = ref<UserAccountItem[]>([
  {
    id: 'usr_1',
    name: 'Kukuh (Suamiku)',
    email: 'kukuh@banglipai.web.id',
    role: 'Superadmin',
    status: 'active',
    lastActive: 'Baru saja',
    authMethod: 'SSO Passkey'
  },
  {
    id: 'usr_2',
    name: 'DevOps Administrator',
    email: 'admin@banglipai.web.id',
    role: 'Admin Account',
    status: 'active',
    lastActive: '5 menit lalu',
    authMethod: 'BFF Session'
  },
  {
    id: 'usr_3',
    name: 'Ahmad Fauzi',
    email: 'fauzi@banglipai.web.id',
    role: 'User',
    status: 'active',
    lastActive: '1 jam lalu',
    authMethod: 'Password'
  },
  {
    id: 'usr_4',
    name: 'Siti Nurhaliza',
    email: 'siti@banglipai.web.id',
    role: 'User',
    status: 'active',
    lastActive: '3 jam lalu',
    authMethod: 'SSO Passkey'
  },
  {
    id: 'usr_5',
    name: 'Budi Santoso',
    email: 'budi@banglipai.web.id',
    role: 'User',
    status: 'suspended',
    lastActive: '3 hari lalu',
    authMethod: 'Password'
  }
])

const userSearch = ref('')
const filteredUsers = computed(() => {
  if (!userSearch.value) return userList.value
  const q = userSearch.value.toLowerCase()
  return userList.value.filter(u => 
    u.name.toLowerCase().includes(q) || 
    u.email.toLowerCase().includes(q) ||
    u.role.toLowerCase().includes(q)
  )
})

// Audit logs stream
const auditLogs = ref([
  { id: 1, action: 'BFF_SESSION_LOGIN', actor: 'Kukuh', role: 'Superadmin', ip: '10.10.10.5', time: '10:42:15', status: 'Success' },
  { id: 2, action: 'NODE_HEALTH_PROBE', actor: 'Karina AI', role: 'System', ip: '10.10.10.99', time: '10:40:00', status: 'Optimal' },
  { id: 3, action: 'USER_ROLE_VERIFY', actor: 'DevOps Admin', role: 'Admin Account', ip: '10.10.10.40', time: '10:35:12', status: 'Success' },
  { id: 4, action: 'SUBNET_SECRET_QUERY', actor: 'Kukuh', role: 'Superadmin', ip: '10.10.10.5', time: '10:28:44', status: 'Authorized' },
  { id: 5, action: 'PASSWORD_ENTROPY_CHECK', actor: 'Ahmad Fauzi', role: 'User', ip: '10.10.10.***', time: '09:15:03', status: 'Passed' }
])

// Self Profile update form
const profileName = ref(authStore.currentUser?.name || '')
const profileEmail = ref(authStore.currentUser?.email || '')
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
  <div class="flex-1 flex min-h-screen">
    
    <!-- Left Navigation Sidebar -->
    <DashboardSidebar :activeTab="activeTab" @update:activeTab="activeTab = $event" />

    <!-- Right Main Workspace -->
    <div class="flex-1 flex flex-col min-w-0">
      
      <DashboardHeader
        :title="currentTitle"
        :subtitle="`Terhubung sebagai ${authStore.currentUser?.name} (${authStore.currentUser?.role})`"
      />

      <!-- Content View Area -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 overflow-y-auto">

        <!-- ============================================== -->
        <!-- TAB 1: OVERVIEW & TELEMETRY -->
        <!-- ============================================== -->
        <div v-if="activeTab === 'overview'" class="space-y-6">
          
          <!-- Welcome Banner -->
          <div class="glass-panel rounded-3xl p-6 sm:p-8 relative overflow-hidden">
            <div class="relative z-10 space-y-3 max-w-2xl">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>BFF Encrypted Session Active</span>
              </div>
              <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Selamat Datang di BangLipai Control Center, {{ authStore.currentUser?.name }}! ✨
              </h2>
              <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                Anda masuk dengan otorisasi <span class="font-bold text-indigo-600 dark:text-sky-400">{{ authStore.currentUser?.role }}</span>. Seluruh operasi hak akses terproteksi oleh Hypervel Coroutine Engine & BangLipai Secure Portal.
              </p>
            </div>
            
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-gradient-to-tr from-indigo-500/20 to-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
          </div>

          <!-- Metric Cards Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="glass-panel rounded-2xl p-5 space-y-2">
              <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-mono font-medium">CLUSTER HEALTH</span>
                <Server class="w-4 h-4 text-emerald-500" />
              </div>
              <p class="text-2xl font-black text-slate-900 dark:text-white">100%</p>
              <div class="flex items-center gap-1.5 text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>8 Nodes Operational</span>
              </div>
            </div>

            <div class="glass-panel rounded-2xl p-5 space-y-2">
              <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-mono font-medium">BFF THROUGHPUT</span>
                <Zap class="w-4 h-4 text-sky-500" />
              </div>
              <p class="text-2xl font-black text-slate-900 dark:text-white">14.2k <span class="text-xs font-normal text-slate-400">rps</span></p>
              <p class="text-[11px] text-slate-500 font-mono">Swoole Coroutine Pool</p>
            </div>

            <div class="glass-panel rounded-2xl p-5 space-y-2">
              <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-mono font-medium">ACTIVE DIRECTORY</span>
                <Users class="w-4 h-4 text-indigo-500" />
              </div>
              <p class="text-2xl font-black text-slate-900 dark:text-white">{{ userList.length }} <span class="text-xs font-normal text-slate-400">users</span></p>
              <p class="text-[11px] text-slate-500 font-mono">Zero-Trust Directory</p>
            </div>

            <div class="glass-panel rounded-2xl p-5 space-y-2">
              <div class="flex items-center justify-between text-slate-400">
                <span class="text-xs font-mono font-medium">AI OPS TELEMETRY</span>
                <Bot class="w-4 h-4 text-rose-500" />
              </div>
              <p class="text-2xl font-black text-slate-900 dark:text-white">Karina</p>
              <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">Monitoring & Active</p>
            </div>

          </div>

          <!-- Role Capability Highlights -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <div
              class="glass-panel rounded-2xl p-5 space-y-3 border-t-4"
              :class="authStore.isSuperadmin ? 'border-t-rose-500' : 'border-t-slate-300 dark:border-t-slate-700 opacity-80'"
            >
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Superadmin Level</h3>
                <Tag value="Full Root" severity="danger" class="!text-[10px]" />
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Akses tanpa batas ke seluruh topologi Proxmox, private subnets (10.10.10.x), dan audit log sistem secara lengkap.
              </p>
              <button
                v-if="authStore.isSuperadmin"
                @click="activeTab = 'cluster'"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
              >
                <span>Buka Klaster & Subnet</span>
                <span>→</span>
              </button>
            </div>

            <div
              class="glass-panel rounded-2xl p-5 space-y-3 border-t-4"
              :class="authStore.canManageUsers ? 'border-t-sky-500' : 'border-t-slate-300 dark:border-t-slate-700 opacity-80'"
            >
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Admin Account Level</h3>
                <Tag value="User Management" severity="info" class="!text-[10px]" />
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Pengelolaan akun, penugasan role pengguna, reset kredensial sandi, dan pemantauan aktivitas user ekosistem.
              </p>
              <button
                v-if="authStore.canManageUsers"
                @click="activeTab = 'users'"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer"
              >
                <span>Buka Manajemen User</span>
                <span>→</span>
              </button>
            </div>

            <div
              class="glass-panel rounded-2xl p-5 space-y-3 border-t-4 border-t-indigo-500"
            >
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">User Self-Service</h3>
                <Tag value="Self Control" severity="secondary" class="!text-[10px]" />
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Pengaturan profil mandiri, registrasi kunci passkey WebAuthn, otentikasi dua faktor (MFA), dan sesi aktif.
              </p>
              <button
                @click="activeTab = 'profile'"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer"
              >
                <span>Buka Pengaturan Profil</span>
                <span>→</span>
              </button>
            </div>

          </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 2: USER MANAGEMENT (SUPERADMIN & ADMIN) -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'users' && authStore.canManageUsers" class="space-y-6">
          
          <div class="glass-panel rounded-3xl p-6 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Daftar Pengguna & Kebijakan Hak Akses</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola akun SSO ekosistem banglipai.web.id</p>
              </div>

              <div class="flex items-center gap-3">
                <div class="relative">
                  <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  <InputText
                    v-model="userSearch"
                    placeholder="Cari user / email / role..."
                    class="!pl-9 !pr-3.5 !py-2 !rounded-xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700"
                  />
                </div>

                <button
                  class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-xs cursor-pointer"
                >
                  <Plus class="w-3.5 h-3.5" />
                  <span>Tambah User</span>
                </button>
              </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
              <DataTable :value="filteredUsers" stripedRows class="p-datatable-sm text-xs font-sans">
                <Column field="name" header="Nama Pengguna">
                  <template #body="{ data }">
                    <div class="flex items-center gap-2.5">
                      <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs">
                        {{ data.name.charAt(0) }}
                      </div>
                      <span class="font-bold text-slate-900 dark:text-white">{{ data.name }}</span>
                    </div>
                  </template>
                </Column>

                <Column field="email" header="Email SSO">
                  <template #body="{ data }">
                    <span class="font-mono text-slate-600 dark:text-slate-300 text-xs">{{ data.email }}</span>
                  </template>
                </Column>

                <Column field="role" header="Peran / Role">
                  <template #body="{ data }">
                    <Tag
                      :value="data.role"
                      :severity="data.role === 'Superadmin' ? 'danger' : (data.role === 'Admin Account' ? 'info' : 'secondary')"
                      class="!text-[10px]"
                    />
                  </template>
                </Column>

                <Column field="authMethod" header="Metode Auth">
                  <template #body="{ data }">
                    <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">{{ data.authMethod }}</span>
                  </template>
                </Column>

                <Column field="status" header="Status">
                  <template #body="{ data }">
                    <span
                      class="inline-flex items-center gap-1 text-[11px] font-semibold"
                      :class="data.status === 'active' ? 'text-emerald-500' : 'text-rose-500'"
                    >
                      <span class="w-1.5 h-1.5 rounded-full" :class="data.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                      <span>{{ data.status.toUpperCase() }}</span>
                    </span>
                  </template>
                </Column>

                <Column field="lastActive" header="Aktivitas Terakhir">
                  <template #body="{ data }">
                    <span class="text-slate-500 text-[11px]">{{ data.lastActive }}</span>
                  </template>
                </Column>

                <Column header="Aksi">
                  <template #body="{ data }">
                    <div class="flex items-center gap-2">
                      <button class="px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-[10px] font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                        Edit
                      </button>
                      <button
                        v-if="data.role !== 'Superadmin'"
                        class="px-2 py-1 rounded-lg border border-rose-200 dark:border-rose-900/50 hover:bg-rose-50 dark:hover:bg-rose-950 text-[10px] font-semibold text-rose-600 cursor-pointer"
                      >
                        Suspend
                      </button>
                    </div>
                  </template>
                </Column>
              </DataTable>
            </div>

          </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 3: CLUSTER & SUBNETS (SUPERADMIN ONLY) -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'cluster' && authStore.isSuperadmin" class="space-y-6">
          
          <div class="glass-panel rounded-3xl p-6 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white">Klaster Home-Lab & Subnet Privat</h3>
                  <Tag value="Superadmin Privilege" severity="danger" class="!text-[10px]" />
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  Pantau node Proxmox, LXC container, dan virtual machines di gateway 10.10.10.5
                </p>
              </div>

              <!-- Subnet Toggle -->
              <button
                @click="clusterStore.toggleSubnetDetails()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold cursor-pointer transition-all shadow-xs"
                :class="clusterStore.showDevDetails 
                  ? 'bg-indigo-600 text-white border border-indigo-600' 
                  : 'bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-300'"
              >
                <Eye v-if="!clusterStore.showDevDetails" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
                <span>{{ clusterStore.showDevDetails ? 'Sembunyikan Subnet IP' : 'Tampilkan Detail Subnet IP' }}</span>
              </button>
            </div>

            <!-- Nodes Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div
                v-for="node in clusterStore.nodes"
                :key="node.id"
                class="p-4 rounded-2xl bg-white/70 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 space-y-3 cursor-pointer hover:border-indigo-500 transition-colors"
                @click="clusterStore.selectNode(node)"
              >
                <div class="flex items-center justify-between">
                  <span class="text-[10px] font-mono uppercase px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    {{ node.type }}
                  </span>
                  <div class="flex items-center gap-1 text-[11px] font-mono text-emerald-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ node.ping }}</span>
                  </div>
                </div>

                <div>
                  <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ node.name }}</h4>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ node.role }}</p>
                </div>

                <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/60 font-mono text-xs flex justify-between items-center">
                  <span class="text-[10px] text-slate-400">IP ADDRESS:</span>
                  <span class="font-bold" :class="clusterStore.showDevDetails ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'">
                    {{ clusterStore.showDevDetails ? node.internalIp : node.maskedIp }}
                  </span>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 4: AUDIT LOGS (ALL ROLES) -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'audit'" class="space-y-6">
          
          <div class="glass-panel rounded-3xl p-6 space-y-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Audit Trail Keamanan & Autentikasi</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Rekaman jejak aktivitas login dan perubahan hak akses</p>
              </div>
              <Tag value="Immutable Log" severity="secondary" class="!text-[10px]" />
            </div>

            <div class="space-y-3 font-mono text-xs">
              <div
                v-for="log in auditLogs"
                :key="log.id"
                class="p-3.5 rounded-2xl bg-white/60 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
              >
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 flex items-center justify-center text-indigo-600 dark:text-sky-400 font-bold shrink-0">
                    <ShieldCheck class="w-4 h-4" />
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-slate-900 dark:text-white">{{ log.action }}</span>
                      <Tag :value="log.role" :severity="log.role === 'Superadmin' ? 'danger' : 'info'" class="!text-[9px]" />
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                      Pelaksana: <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ log.actor }}</span> • Origin IP: {{ authStore.canAccessSubnets ? log.ip : '10.10.10.***' }}
                    </p>
                  </div>
                </div>

                <div class="flex items-center gap-3 self-end sm:self-auto">
                  <span class="text-emerald-500 font-semibold text-[11px]">{{ log.status }}</span>
                  <span class="text-slate-400 text-[11px]">{{ log.time }}</span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 5: AI OPS TELEMETRY KARINA (SUPERADMIN) -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'ai_ops' && authStore.isSuperadmin" class="space-y-6">
          
          <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500">
                <Bot class="w-6 h-6" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Karina AI Ops Autonomous Intelligence</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Node: 10.10.10.99 (Zone-AI) • Memory Coroutine Resident</p>
              </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-900 text-slate-100 font-mono text-xs space-y-2">
              <div class="text-emerald-400 font-bold">✨ [Karina AI Agent Active Status]:</div>
              <p class="text-slate-300">» All 8 cluster nodes are operating within optimal latency thresholds (&lt; 2.0ms).</p>
              <p class="text-slate-300">» Swarm execution memory footprint is stable at 68MB pooled coroutines.</p>
              <p class="text-slate-300">» No unauthorized subnet penetration attempts detected.</p>
            </div>
          </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 6: PROFILE & SELF-SERVICE -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'profile'" class="space-y-6">
          
          <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6 max-w-3xl">
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">Profil & Keamanan Akun Anda</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">Kelola identitas, kata sandi, dan kunci akses WebAuthn</p>
            </div>

            <div v-if="profileToastMessage" class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
              <CheckCircle2 class="w-4 h-4 shrink-0" />
              <span>{{ profileToastMessage }}</span>
            </div>

            <form @submit.prevent="saveProfile" class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Nama Lengkap
                </label>
                <InputText
                  v-model="profileName"
                  class="w-full !px-3.5 !py-2.5 !rounded-xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Email SSO Terdaftar
                </label>
                <InputText
                  v-model="profileEmail"
                  disabled
                  class="w-full !px-3.5 !py-2.5 !rounded-xl !text-xs !bg-slate-100 dark:!bg-slate-800 !border-slate-300 dark:!border-slate-700 !opacity-70 font-mono"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                  Ganti Password Baru (Opsional)
                </label>
                <Password
                  v-model="profilePassword"
                  toggleMask
                  placeholder="Ketik password baru..."
                  class="w-full"
                  inputClass="w-full !px-3.5 !py-2.5 !rounded-xl !text-xs !bg-slate-50 dark:!bg-slate-900 !border-slate-300 dark:!border-slate-700 font-mono"
                />
              </div>

              <div class="pt-2">
                <button
                  type="submit"
                  class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition-all cursor-pointer"
                >
                  Simpan Perubahan
                </button>
              </div>
            </form>

            <div class="border-t border-slate-200 dark:border-slate-800/80 pt-6 space-y-3">
              <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Kunci Keamanan WebAuthn / Passkey</h4>
              <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <KeyRound class="w-4 h-4 text-emerald-500" />
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
  </div>
</template>
