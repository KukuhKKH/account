import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware(async (to, from) => {
  const authStore = useAuthStore()

  // Make sure session is fully initialized with /me check
  if (!authStore.isInitialized) {
    await authStore.initSession(false)
  }

  // If still not logged in, redirect to home page and trigger SSO login modal
  if (!authStore.isLoggedIn) {
    authStore.openSsoModal()
    return navigateTo('/')
  }
})

