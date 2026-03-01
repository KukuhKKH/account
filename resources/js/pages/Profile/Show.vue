<template>
  <AppLayout>
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
          My Profile
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your account information</p>
      </div>

      <!-- Profile Info -->
      <div class="mx-auto max-w-2xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="grid gap-6 md:grid-cols-2">
          <!-- Avatar -->
          <div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Avatar</p>
            <div class="mt-4 flex justify-center">
              <img
                v-if="user.avatar"
                :src="user.avatar"
                :alt="user.name"
                class="h-24 w-24 rounded-full"
              />
              <div v-else class="flex h-24 w-24 items-center justify-center rounded-full bg-blue-600 text-4xl font-medium text-white">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
            </div>
          </div>

          <!-- Basic Info -->
          <div class="space-y-4">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Name</p>
              <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                {{ user.name }}
              </p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</p>
              <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                {{ user.email }}
              </p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Role</p>
              <span
                class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-medium"
                  :class="getRoleBadgeClass(user.roles)"
                >
                  {{ getRoleDisplay(user.roles) }}
              </span>
            </div>
          </div>
        </div>

        <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-800">
          <Link
            :href="`/users/${user.id}/edit`"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2 font-medium text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
          >
            <Pencil class="-ml-1 mr-2 h-5 w-5" />
            Edit Profile
          </Link>
        </div>
      </div>

      <!-- Contact Information -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Contact Information</h3>
        <div class="mt-4 space-y-4">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Phone</p>
            <p class="mt-1 text-gray-900 dark:text-white">{{ user.phone || 'Not provided' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Address</p>
            <p class="mt-1 text-gray-900 dark:text-white">{{ user.address || 'Not provided' }}</p>
          </div>
        </div>
      </div>

      <!-- Account Details -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Account Details</h3>
        <div class="mt-4 space-y-4">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Member Since</p>
            <p class="mt-1 text-gray-900 dark:text-white">
              {{ formatDate(user.created_at) }}
            </p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Last Login</p>
            <p class="mt-1 text-gray-900 dark:text-white">
              {{ formatDate(user.last_login_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Recent Sign-Ins -->
      <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Sign-Ins</h3>
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
            No sign-in history yet
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
import { User } from '@/types/auth'
import { useAuth } from '@/composables/useAuth'

const { getRoleBadgeClass, getRoleDisplay } = useAuth()
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
