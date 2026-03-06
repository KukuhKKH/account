<template>
  <AppLayout>
    <div class="mx-auto max-w-7xl space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
            My Profile
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your account information and security</p>
        </div>
        <Link
          :href="`/users/${user.id}/edit`"
          class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
        >
          <Pencil class="h-4 w-4" />
          Edit Profile
        </Link>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Left Column - Sticky -->
        <div class="lg:col-span-1">
          <div class="space-y-6 lg:sticky lg:top-6">
            <!-- Profile Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col items-center text-center">
              <!-- Avatar -->
              <div class="relative">
                <img
                  v-if="user.avatar"
                  :src="user.avatar"
                  :alt="user.name"
                  class="h-24 w-24 rounded-full ring-4 ring-gray-100 dark:ring-gray-800"
                />
                <div v-else class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 text-3xl font-bold text-white ring-4 ring-gray-100 dark:ring-gray-800">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
              </div>

              <!-- User Info -->
              <div class="mt-4 w-full">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                  {{ user.name }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                  {{ user.email }}
                </p>

                <!-- Role Badge -->
                <div class="mt-3">
                  <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                    :class="getRoleBadgeClass(user.roles)"
                  >
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    {{ getRoleDisplay(user.roles) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Quick Stats -->
            <div class="mt-6 grid grid-cols-2 gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
              <div class="text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ formatDate(user.created_at, 'short') }}
                </p>
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Member Since</p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ formatDate(user.last_login_at, 'time') }}
                </p>
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Last Login</p>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
              <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              Contact Info
            </h3>
            <div class="mt-4 space-y-3">
              <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                  <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Phone</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ user.phone || 'Not provided' }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                  <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Address</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user.address || 'Not provided' }}</p>
                </div>
              </div>
            </div>
          </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6 lg:col-span-2">
          <!-- Password & Security -->
          <PasswordSecurityCard
            :user="user"
            :passwordChangeLogs="passwordChangeLogs"
          />

          <!-- Recent Sign-Ins -->
          <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
              <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Recent Sign-Ins
              </h3>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/50">
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                      Date & Time
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                      IP Address
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                      Browser
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                      Operating System
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                  <tr
                    v-for="log in signInLogs.data"
                    :key="log.id"
                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                  >
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                      {{ formatDate(log.signed_in_at) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                      <code class="rounded bg-gray-100 px-2 py-0.5 text-xs font-mono dark:bg-gray-800">
                        {{ log.ip_address || '-' }}
                      </code>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                      {{ log.device_info?.browser || '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                      {{ log.device_info?.os || '-' }}
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="signInLogs.data.length === 0" class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No sign-in history yet</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Pencil } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import PasswordSecurityCard from '@/components/PasswordSecurityCard.vue'
import { User } from '@/types/auth'
import { useAuth } from '@/composables/useAuth'

const { getRoleBadgeClass, getRoleDisplay } = useAuth()
const page = usePage()
const user = computed(() => page.props.user as User)
const signInLogs = computed(() => page.props.signInLogs)
const passwordChangeLogs = computed(() => page.props.passwordChangeLogs as any[])

const formatDate = (date: string | null | undefined, type: 'default' | 'short' | 'time' = 'default') => {
  if (!date) return '-'

  const d = new Date(date)

  if (type === 'short') {
    return d.toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'short',
    })
  }

  if (type === 'time') {
    return d.toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  return d.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
