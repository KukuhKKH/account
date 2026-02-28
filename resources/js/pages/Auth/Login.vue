<template>
  <!-- Toast Notifications -->
  <div class="flex h-screen items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100 dark:from-gray-900 dark:to-gray-800">
    <ToastContainer />
    <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white p-8 shadow-lg dark:border-gray-700 dark:bg-gray-800">
      <!-- Logo -->
      <div class="mb-8 flex justify-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-600">
          <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
      </div>

      <!-- Title -->
      <h1 class="mb-2 text-center text-3xl font-bold text-gray-900 dark:text-white">Account</h1>
      <p class="mb-8 text-center text-gray-600 dark:text-gray-400">BangLipai Secure Portal</p>

      <!-- Divider -->
      <div class="mb-8 flex items-center gap-3">
        <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
        <span class="text-sm text-gray-500 dark:text-gray-400">Sign In</span>
        <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
      </div>

      <!-- Login Button -->
      <a
        :href="route('auth.login')"
        class="group flex w-full items-center justify-center gap-3 rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition-all hover:bg-blue-700 active:scale-95"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
          />
        </svg>
        <span>Login to BangLipai Secure Portal</span>
      </a>

      <!-- Info -->
      <p class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
        Login dengan akun BangLipai Secure Portal Anda untuk mengakses sistem manajemen user ini.
      </p>

      <!-- Security Notice -->
      <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
        <p class="text-xs text-blue-800 dark:text-blue-200">
          <strong>🔒 Secure:</strong> Anda akan diarahkan ke BangLipai Secure Portal untuk login yang aman. Token disimpan server-side.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { route } from 'ziggy-js';
import ToastContainer from '@/components/ToastContainer.vue';
</script>
