<template>
  <div class="flex h-screen flex-col bg-gray-50 dark:bg-gray-950">
    <!-- Toast Notifications -->
    <ToastContainer />

    <!-- Progress Bar -->
    <div
      v-if="isLoading"
      class="fixed top-0 left-0 z-50 h-1 w-full origin-left bg-linear-to-r from-blue-600 via-blue-500 to-indigo-600 progress-bar"
    />

    <!-- Navbar -->
    <nav class="sticky top-0 z-40 border-b border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
      <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Logo & Sidebar Toggle -->
        <div class="flex items-center gap-3">
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="rounded-lg p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 lg:hidden"
          >
            <Menu v-if="!sidebarOpen" class="h-5 w-5" />
            <X v-else class="h-5 w-5" />
          </button>
          <Link href="/" class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
              <Zap class="h-5 w-5 text-white" />
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
                v-if="auth.user?.avatar"
                :src="auth.user.avatar"
                :alt="auth.user.name"
                class="h-6 w-6 rounded-full"
              />
              <div v-else class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-medium text-white">
                {{ auth.user?.name.charAt(0).toUpperCase() }}
              </div>
              <span class="hidden sm:inline">{{ auth.user?.name }}</span>
              <ChevronDown class="h-4 w-4" />
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
    </nav>

    <!-- Main Content -->
    <div class="flex flex-1 overflow-hidden">
      <!-- Mobile Overlay -->
      <div
        v-show="sidebarOpen"
        class="fixed inset-0 z-20 bg-black/50 transition-opacity lg:hidden"
        @click="sidebarOpen = false"
      />

      <!-- Sidebar -->
      <aside
        class="w-64 border-r border-gray-200 bg-white transition-all duration-300 dark:border-gray-800 dark:bg-gray-900 lg:relative"
        :class="{
          'fixed inset-y-0 left-0 z-30 translate-x-0': sidebarOpen,
          'absolute -translate-x-full lg:translate-x-0': !sidebarOpen
        }"
      >
        <div class="space-y-2 p-4">
          <!-- Dashboard Link -->
          <Link
            href="/dashboard"
            class="flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
            :class="{
              'bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200': route().current('dashboard'),
              'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800': !route().current('dashboard')
            }"
            @click="closeSidebarOnLink"
          >
            <LayoutGrid class="h-5 w-5" />
            Dashboard
          </Link>

          <!-- User Management (Admin Only) -->
          <div v-if="auth.user?.role !== 'user'" class="space-y-2">
            <p class="px-4 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-600">Management</p>
            <Link
              href="/users"
              class="flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
              :class="{
                'bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-200': route().current('users.*'),
                'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800': !route().current('users.*')
              }"
              @click="closeSidebarOnLink"
            >
              <Users class="h-5 w-5" />
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
            @click="closeSidebarOnLink"
          >
            <Settings class="h-5 w-5" />
            Account Settings
          </Link>
        </div>
      </aside>

      <!-- Content Area -->
      <main class="flex-1 overflow-auto">
        <Transition
          enter-active-class="page-transition-enter"
          leave-active-class="page-transition-exit"
          mode="out-in"
        >
          <div :key="$page.url" class="h-full">
            <slot />
          </div>
        </Transition>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, Transition } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { Menu, X, Zap, ChevronDown, LayoutGrid, Users, Settings } from 'lucide-vue-next'
import ToastContainer from '@/components/ToastContainer.vue'

const page = usePage()
const auth = computed(() => page.props.auth)
const userMenuOpen = ref(false)
const sidebarOpen = ref(true)
const userMenuRef = ref(null)
const isLoading = ref(false)

const logout = async (e: Event) => {
  e.preventDefault()
  router.post(route('auth.logout'))
}

// Close user menu when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const element = userMenuRef.value as HTMLElement | null
  if (element && !element.contains(event.target as Node)) {
    userMenuOpen.value = false
  }
}

// Close sidebar when clicking a link on mobile
const closeSidebarOnLink = () => {
  if (window.innerWidth < 1024) {
    sidebarOpen.value = false
  }
}

// Handle Inertia navigation for loading state
const handleNavigationStart = () => {
  isLoading.value = true
}

const handleNavigationFinish = () => {
  isLoading.value = false
}

let unsubscribeBefore: (() => void) | null = null
let unsubscribeFinish: (() => void) | null = null

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  unsubscribeBefore = router.on('before', handleNavigationStart)
  unsubscribeFinish = router.on('finish', handleNavigationFinish)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (unsubscribeBefore) unsubscribeBefore()
  if (unsubscribeFinish) unsubscribeFinish()
})
</script>
