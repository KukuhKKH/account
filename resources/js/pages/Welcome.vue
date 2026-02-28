<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';
import type { Auth } from '../types/auth';
import ToastContainer from '@/components/ToastContainer.vue';

const page = usePage<{ auth: Auth }>()
const auth = computed(() => page.props.auth)
</script>

<template>
  <Head title="Welcome" />
  <!-- Toast Notifications -->
  <ToastContainer />
  <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 p-6 dark:from-gray-900 dark:to-gray-800">
    <div class="text-center">
      <!-- Logo -->
      <div class="mb-8 flex justify-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600">
          <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
      </div>

      <!-- Title -->
      <h1 class="text-5xl font-bold text-gray-900 dark:text-white">Account Management</h1>
      <p class="mt-4 text-xl text-gray-600 dark:text-gray-400">
        Manage profile with ease using our secure portal powered by BangLipai authentication.
      </p>

      <!-- Action Buttons -->
      <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center">
        <div v-if="auth.user">
          <Link
            href="/dashboard"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
          >
            Go to Dashboard
            <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </Link>
        </div>
        <div v-else>
          <!-- Plain anchor tag for OAuth redirect (not Inertia Link) to allow full page navigation -->
          <a
            :href="route('auth.login')"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
          >
            Enter BangLipai Secure Portal
            <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </a>
        </div>
      </div>

      <!-- Features -->
      <div class="mt-20 grid gap-8 sm:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
          <div class="flex justify-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
              <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 4H9m6 16H9m6-5a4 4 0 11-8 0m8 0v5" />
              </svg>
            </div>
          </div>
          <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">User Management</h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Create, edit, and manage users with granular role-based access control
          </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
          <div class="flex justify-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
              <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
              </svg>
            </div>
          </div>
          <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">BangLipai Secure Portal</h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Secure authentication with BangLipai Secure Portal, token caching, and BFF pattern
          </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
          <div class="flex justify-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
              <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
          <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">Analytics</h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Track user activity, sign-in logs, and device information in real-time
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
