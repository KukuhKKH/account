<template>
  <AppLayout>
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
            {{ user.name }}
          </h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">{{ user.email }}</p>
        </div>
        <Link
          :href="`/users/${user.id}/edit`"
          class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2 font-medium text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
        >
          <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit
        </Link>
      </div>

      <!-- User Info Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Avatar -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avatar</p>
          <div class="mt-4 flex justify-center">
            <img
              v-if="user.avatar"
              :src="user.avatar"
              :alt="user.name"
              class="h-16 w-16 rounded-full"
            />
            <div v-else class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-2xl font-medium text-white">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
          </div>
        </div>

        <!-- Role -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Role</p>
          <span
            class="mt-4 inline-flex rounded-full px-3 py-1 text-sm font-medium"
            :class="{
              'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': user.role === 'superadmin',
              'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': user.role === 'admin',
              'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': user.role === 'user'
            }"
          >
            {{ user.role }}
          </span>
        </div>

        <!-- Phone -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Phone</p>
          <p class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
            {{ user.phone || '-' }}
          </p>
        </div>

        <!-- Last Login -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Last Login</p>
          <p class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
            {{ formatDate(user.last_login_at) }}
          </p>
        </div>
      </div>

      <!-- Address Info -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Address</h3>
        <p class="mt-4 text-gray-600 dark:text-gray-400">
          {{ user.address || 'No address provided' }}
        </p>
      </div>

      <!-- Sign-In History -->
      <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sign-In History</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-800">
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                  Date & Time
                </th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                  IP Address
                </th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                  Browser
                </th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                  OS
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="log in signInLogs.data"
                :key="log.id"
                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
              >
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                  {{ formatDate(log.signed_in_at) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                  {{ log.ip_address || '-' }}
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
          <div v-if="signInLogs.data.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
            No sign-in history
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="signInLogs.links && signInLogs.links.length > 1" class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Showing <span class="font-medium">{{ signInLogs.from }}</span> to
              <span class="font-medium">{{ signInLogs.to }}</span>
            </p>
            <div class="flex gap-2">
              <template v-for="link in signInLogs.links" :key="link.label">
                <Link
                  v-if="link.url && link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                  :href="link.url"
                  :class="{
                    'bg-blue-600 text-white': link.active,
                    'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300': !link.active
                  }"
                  class="inline-flex h-10 w-10 items-center justify-center rounded-lg font-medium"
                >
                  {{ link.label }}
                </Link>
              </template>
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
import AppLayout from '@/layouts/AppLayout.vue'
import { User, SignInLog } from '@/types/auth'

const page = usePage()
const user = computed(() => page.props.user as User)
const signInLogs = computed(() => page.props.signInLogs)

const formatDate = (date: string | null | undefined) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
