import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore()

  if (!authStore.isLoggedIn) {
    authStore.initSession()
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
