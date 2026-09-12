import { defineStore } from 'pinia'
import type { AuthUser, UserRoleType, LoginCredentials } from '~/types/auth'

export const useAuthStore = defineStore('auth', () => {
  const currentUser = ref<AuthUser | null>(null)
  const ssoDialogVisible = ref(false)
  const isLoading = ref(false)

  // Getters
  const isLoggedIn = computed(() => currentUser.value !== null)
  const role = computed<UserRoleType | null>(() => currentUser.value?.role ?? null)
  
  // RBAC Capabilities
  // 1. Superadmin: Full power (Subnets, Cluster nodes, Full system settings, all user management)
  const isSuperadmin = computed(() => currentUser.value?.role === 'Superadmin')
  
  // 2. Admin Account: User management and accounts only (cannot access internal subnets/infra secrets)
  const isAdminAccount = computed(() => currentUser.value?.role === 'Admin Account')
  
  // 3. User: Self profile, self keys, personal session
  const isUser = computed(() => currentUser.value?.role === 'User')

  // Permission Checks
  const canAccessSubnets = computed(() => isSuperadmin.value)
  const canManageUsers = computed(() => isSuperadmin.value || isAdminAccount.value)
  const canManageSystem = computed(() => isSuperadmin.value)
  const canAccessDashboard = computed(() => isLoggedIn.value)

  // Actions
  function initSession() {
    if (typeof window !== 'undefined') {
      const savedAuth = localStorage.getItem('banglipai_session')
      if (savedAuth) {
        try {
          currentUser.value = JSON.parse(savedAuth)
        } catch {
          currentUser.value = null
        }
      }
    }
  }

  function login(credentials: LoginCredentials) {
    isLoading.value = true
    const role = credentials.role || 'Superadmin'
    
    const roleProfiles: Record<UserRoleType, { name: string, email: string, phone: string }> = {
      'Superadmin': {
        name: 'Kukuh (Suamiku)',
        email: credentials.email || 'kukuh@banglipai.web.id',
        phone: '+62 812-3456-7890'
      },
      'Admin Account': {
        name: 'DevOps Administrator',
        email: credentials.email || 'admin@banglipai.web.id',
        phone: '+62 811-9876-5432'
      },
      'User': {
        name: 'Member BangLipai',
        email: credentials.email || 'user@banglipai.web.id',
        phone: '+62 813-5555-0199'
      }
    }

    const profile = roleProfiles[role]

    currentUser.value = {
      id: `usr_${Math.random().toString(36).substring(2, 9)}`,
      name: profile.name,
      email: profile.email,
      role: role,
      phone: profile.phone,
      lastLoginAt: new Date().toISOString(),
      mfaEnabled: role !== 'User'
    }

    if (typeof window !== 'undefined') {
      localStorage.setItem('banglipai_session', JSON.stringify(currentUser.value))
    }

    ssoDialogVisible.value = false
    isLoading.value = false
  }

  function logout() {
    currentUser.value = null
    if (typeof window !== 'undefined') {
      localStorage.removeItem('banglipai_session')
    }
  }

  function openSsoModal() {
    ssoDialogVisible.value = true
  }

  function closeSsoModal() {
    ssoDialogVisible.value = false
  }

  return {
    currentUser,
    ssoDialogVisible,
    isLoading,
    isLoggedIn,
    role,
    isSuperadmin,
    isAdminAccount,
    isUser,
    canAccessSubnets,
    canManageUsers,
    canManageSystem,
    canAccessDashboard,
    initSession,
    login,
    logout,
    openSsoModal,
    closeSsoModal
  }
})
