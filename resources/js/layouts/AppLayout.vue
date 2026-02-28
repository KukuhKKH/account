<template>
  <div class="flex h-screen flex-col bg-gray-50 dark:bg-gray-950">
    <!-- Toast Notifications -->
    <ToastContainer />
    <!-- Navbar -->
    <nav class="border-b border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
          <!-- Logo -->
          <div class="flex items-center">
            <Link href="/" class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <span class="text-lg">Account</span>
            </Link>
          </div>

          <!-- Right Menu -->
          <div class="flex items-center gap-4">
            <!-- User Menu -->
            <div class="relative" ref="userMenuRef">
              <button
                @click="userMenuOpen = !userMenuOpen"
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
              >
                <img
                  v-if="auth.user.avatar"
                  :src="auth.user.avatar"
                  :alt="auth.user.name"
                  class="h-6 w-6 rounded-full"
                />
                <div v-else class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-medium text-white">
                  {{ auth.user.name.charAt(0).toUpperCase() }}
                </div>
                <span class="hidden sm:inline">{{ auth.user.name }}</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
              </button>

              <!-- User Menu Dropdown -->
              <div
                v-show="userMenuOpen"
                class="absolute right-0 mt-2 w-48 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-800"
              >
                <Link
                  href="/profile"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                  View Profile
                </Link>
                <a
                  href="#"
                  @click="logout"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                  Sign Out
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar -->
      <aside class="w-64 border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="space-y-2 p-4">
          <!-- Dashboard Link -->
          <Link
            href="/dashboard"
            class="flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
            :class="{
              'bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200': route().current('dashboard'),
              'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800': !route().current('dashboard')
            }"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 16l-7-4m0 0l-2-3m2 3v-10" />
            </svg>
            Dashboard
          </Link>

          <!-- User Management (Admin Only) -->
          <div v-if="auth.user.role !== 'user'" class="space-y-2">
            <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-600">Management</p>
            <Link
              href="/users"
              class="flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
              :class="{
                'bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200': route().current('users.*'),
                'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800': !route().current('users.*')
              }"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 4H9m6 16H9m6-5a4 4 0 11-8 0m8 0v5" />
              </svg>
              Users
            </Link>
          </div>

          <!-- Settings -->
          <p class="mt-8 px-4 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-600">Settings</p>
          <Link
            href="/profile"
            class="flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
            :class="{
              'bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200': route().current('profile.*'),
              'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800': !route().current('profile.*')
            }"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Account Settings
          </Link>
        </div>
      </aside>

      <!-- Content Area -->
      <main class="flex-1 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import ToastContainer from '@/components/ToastContainer.vue'

const page = usePage()
const auth = computed(() => page.props.auth)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)

const logout = async (e: Event) => {
  e.preventDefault()
  router.post(route('auth.logout'))
}

// Close user menu when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
    userMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
