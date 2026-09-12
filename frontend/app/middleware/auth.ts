import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore()

  // Make sure session is initialized
  if (!authStore.isLoggedIn) {
    authStore.initSession()
  }

  // If still not logged in, redirect to home page and trigger SSO login modal
  if (!authStore.isLoggedIn) {
    authStore.openSsoModal()
    return navigateTo('/')
  }
})
