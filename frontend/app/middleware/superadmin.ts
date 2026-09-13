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

  // Superadmin guard
  if (!authStore.isSuperadmin) {
    return navigateTo('/dashboard')
  }
})

