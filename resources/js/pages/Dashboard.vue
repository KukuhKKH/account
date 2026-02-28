<template>
  <AppLayout>
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div>
        <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
          Dashboard
        </h1>
        <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
          Selamat datang, {{ auth.user.name }}
        </p>
      </div>

      <!-- Statistics Grid -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Users Card -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Total Users
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ statistics.total_users }}
              </p>
            </div>
            <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900">
              <svg
                class="h-6 w-6 text-blue-600 dark:text-blue-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 4H9m6 16H9m6-5a4 4 0 11-8 0m8 0v5"
                />
              </svg>
            </div>
          </div>
        </div>

        <!-- Superadmins Card -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Superadmins
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ statistics.superadmins }}
              </p>
            </div>
            <div class="rounded-lg bg-purple-100 p-3 dark:bg-purple-900">
              <svg
                class="h-6 w-6 text-purple-600 dark:text-purple-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 10V3L4 14h7v7l9-11h-7z"
                />
              </svg>
            </div>
          </div>
        </div>

        <!-- Admins Card -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Admins
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ statistics.admins }}
              </p>
            </div>
            <div class="rounded-lg bg-green-100 p-3 dark:bg-green-900">
              <svg
                class="h-6 w-6 text-green-600 dark:text-green-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"
                />
              </svg>
            </div>
          </div>
        </div>

        <!-- Regular Users Card -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                Regular Users
              </p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ statistics.regular_users }}
              </p>
            </div>
            <div class="rounded-lg bg-orange-100 p-3 dark:bg-orange-900">
              <svg
                class="h-6 w-6 text-orange-600 dark:text-orange-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 4H9m6 16H9m6-5a4 4 0 11-8 0m8 0v5"
                />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Secondary Stats -->
      <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
            Total Sign-Ins
          </h3>
          <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
            {{ statistics.total_sign_ins }}
          </p>
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Avg {{ statistics.average_sign_ins_per_user }} per user
          </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">
            Quick Actions
          </h3>
          <div class="mt-4 flex gap-2">
            <Link
              v-if="auth.user.role !== 'user'"
              href="/users/create"
              class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
            >
              <svg
                class="-ml-1 mr-2 h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              New User
            </Link>
            <Link
              href="/profile"
              class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
            >
              Edit Profile
            </Link>
          </div>
        </div>
      </div>

      <!-- Recent Sign-Ins Table -->
      <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Recent Sign-Ins
          </h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-800">
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                  User
                </th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                  IP Address
                </th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                  Device
                </th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                  Time
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="user in recentSignIns"
                :key="user.id"
                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img
                      v-if="user.avatar"
                      :src="user.avatar"
                      :alt="user.name"
                      class="h-8 w-8 rounded-full"
                    />
                    <div v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                      <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ user.name }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ user.email }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                  {{ user.signInLogs?.[0]?.ip_address || '-' }}
                </td>
                <td class="px-6 py-4">
                  <div v-if="user.signInLogs?.[0]?.device_info" class="text-sm text-gray-700 dark:text-gray-300">
                    <p>{{ user.signInLogs[0].device_info.browser }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ user.signInLogs[0].device_info.os }}
                    </p>
                  </div>
                  <span v-else class="text-sm text-gray-500 dark:text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(user.last_login_at) }}
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="recentSignIns.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
            No recent sign-ins
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { User, UserStatistics } from '@/types/auth'

const page = usePage()
const auth = computed(() => page.props.auth)
const statistics = computed(() => page.props.statistics as UserStatistics)
const recentSignIns = computed(() => page.props.recentSignIns as User[])

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
