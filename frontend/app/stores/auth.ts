import { defineStore } from 'pinia'
import type { AuthUser, UserRoleType, LoginCredentials, ApiMeResponse } from '~/types/auth'

export const useAuthStore = defineStore('auth', () => {
  const currentUser = ref<AuthUser | null>(null)
  const ssoDialogVisible = ref(false)
  const isLoading = ref(false)
  const isCheckingSession = ref(false)
  const isInitialized = ref(false)

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

  let activeFetchPromise: Promise<boolean> | null = null

  /**
   * Fetch current authenticated session from BFF backend (/me endpoint)
   */
  async function fetchMe(force = false): Promise<boolean> {
    // Return existing active promise if already in flight (deduplication)
    if (activeFetchPromise) {
      return activeFetchPromise
    }

    // Skip network call if already initialized and not forced
    if (isInitialized.value && !force) {
      return isLoggedIn.value
    }

    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase || 'https://api-identity.home.test'
    isCheckingSession.value = true

    activeFetchPromise = (async () => {
      try {
        const res = await $fetch<ApiMeResponse>(`${apiBase}/me`, {
          method: 'GET',
          credentials: 'include',
          headers: {
            'Accept': 'application/json'
          }
        })

        if (res && res.authenticated && res.user) {
          const u = res.user
          let userRole: UserRoleType = 'User'
          const roles: string[] = Array.isArray(u.roles) ? u.roles : []

          if (roles.includes('Superadmin')) {
            userRole = 'Superadmin'
          } else if (roles.includes('Admin Account') || roles.includes('Admin')) {
            userRole = 'Admin Account'
          }

          currentUser.value = {
            id: String(u.id),
            name: u.name || 'Pengguna',
            email: u.email,
            role: userRole,
            phone: u.phone ?? undefined,
            avatarUrl: u.avatar ?? undefined,
            lastLoginAt: u.last_login_at || new Date().toISOString(),
            mfaEnabled: userRole !== 'User'
          }

          if (typeof window !== 'undefined') {
            localStorage.setItem('banglipai_session', JSON.stringify(currentUser.value))
          }

          return true
        } else {
          currentUser.value = null
          if (typeof window !== 'undefined') {
            localStorage.removeItem('banglipai_session')
          }
          return false
        }
      } catch {
        // Fallback cache if backend is temporarily unreachable or offline dev mode
        if (typeof window !== 'undefined') {
          const saved = localStorage.getItem('banglipai_session')
          if (saved) {
            try {
              currentUser.value = JSON.parse(saved)
              return true
            } catch {
              currentUser.value = null
            }
          }
        }
        currentUser.value = null
        return false
      } finally {
        isCheckingSession.value = false
        isInitialized.value = true
        activeFetchPromise = null
      }
    })()

    return activeFetchPromise
  }

  /**
   * Initialize session on app load or refresh
   */
  async function initSession(force = false): Promise<boolean> {
    // If already initialized in Pinia, use stored state without extra network calls
    if (isInitialized.value && !force) {
      return isLoggedIn.value
    }

    // Quick load from localStorage to avoid initial layout flash
    if (typeof window !== 'undefined' && !currentUser.value) {
      const savedAuth = localStorage.getItem('banglipai_session')
      if (savedAuth) {
        try {
          currentUser.value = JSON.parse(savedAuth)
        } catch {
          currentUser.value = null
        }
      }
    }

    // Call /me to verify real session cookie on the BFF backend
    return await fetchMe(force)
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

    if (typeof window !== 'undefined') {
      const route = useRoute()
      if (route.path === '/') {
        navigateTo('/dashboard')
      }
    }
  }

  function redirectToLogin() {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase || 'https://api-identity.home.test'
    if (typeof window !== 'undefined') {
      window.location.href = `${apiBase}/auth/login`
    }
  }

  function redirectToLogout() {
    const config = useRuntimeConfig()
    const apiBase = config.public.apiBase || 'https://api-identity.home.test'
    currentUser.value = null
    if (typeof window !== 'undefined') {
      localStorage.removeItem('banglipai_session')
      window.location.href = `${apiBase}/auth/logout`
    }
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
    isCheckingSession,
    isInitialized,
    isLoggedIn,
    role,
    isSuperadmin,
    isAdminAccount,
    isUser,
    canAccessSubnets,
    canManageUsers,
    canManageSystem,
    canAccessDashboard,
    fetchMe,
    initSession,
    login,
    logout,
    redirectToLogin,
    redirectToLogout,
    openSsoModal,
    closeSsoModal
  }
})

