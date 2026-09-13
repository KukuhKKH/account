import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware(async (to, from) => {
  const authStore = useAuthStore()

  if (!authStore.isInitialized) {
    await authStore.initSession(false)
  }

  if (!authStore.isLoggedIn) {
    authStore.openSsoModal()
    return navigateTo('/')
  }

  // Admin Account & Superadmin guard
  if (!authStore.canManageUsers) {
    return navigateTo('/dashboard')
  }
})
