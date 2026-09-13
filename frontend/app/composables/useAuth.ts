import { storeToRefs } from 'pinia'
import { useAuthStore } from '~/stores/auth'
import type { LoginCredentials, UserRoleType } from '~/types/auth'

/**
 * Clean Composable wrapper for BangLipai Identity Authentication & RBAC
 * Provides reactive access to auth state, user profile, capabilities, and session actions.
 */
export function useAuth() {
  const authStore = useAuthStore()

  // Reactive state & getters via storeToRefs for direct destructuring
  const {
    currentUser: user,
    isLoggedIn,
    role,
    isSuperadmin,
    isAdminAccount,
    isUser,
    canAccessSubnets,
    canManageUsers,
    canManageSystem,
    canAccessDashboard,
    isLoading,
    isCheckingSession,
    isInitialized,
    ssoDialogVisible
  } = storeToRefs(authStore)

  // Actions
  const initSession = (force = false) => authStore.initSession(force)
  const refreshSession = () => authStore.fetchMe(true)
  const login = (credentials: LoginCredentials) => authStore.login(credentials)
  const logout = () => authStore.logout()
  const redirectToLogin = () => authStore.redirectToLogin()
  const redirectToLogout = () => authStore.redirectToLogout()
  const openSsoModal = () => authStore.openSsoModal()
  const closeSsoModal = () => authStore.closeSsoModal()

  return {
    // State & Getters
    user,
    isLoggedIn,
    role,
    isSuperadmin,
    isAdminAccount,
    isUser,
    canAccessSubnets,
    canManageUsers,
    canManageSystem,
    canAccessDashboard,
    isLoading,
    isCheckingSession,
    isInitialized,
    ssoDialogVisible,

    // Actions
    initSession,
    refreshSession,
    login,
    logout,
    redirectToLogin,
    redirectToLogout,
    openSsoModal,
    closeSsoModal,

    // Raw store instance if needed
    authStore
  }
}
