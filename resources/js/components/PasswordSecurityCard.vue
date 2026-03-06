<template>
  <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
    <!-- Header - Clickable -->
    <button
      @click="toggleAccordion"
      class="w-full p-6 border-b border-gray-200 dark:border-gray-800 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 hover:from-blue-100 hover:to-indigo-100 dark:hover:from-blue-900/30 dark:hover:to-indigo-900/30 transition-all cursor-pointer"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          <div class="text-left">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
              Password & Security
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              Manage your password securely through BangLipai Secure Portal
            </p>
          </div>
        </div>
        <!-- Chevron Icon -->
        <svg
          class="h-5 w-5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
          :class="{ 'rotate-180': isExpanded }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </button>

    <!-- Content - Collapsible -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="max-h-0 opacity-0"
      enter-to-class="max-h-[2000px] opacity-100"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="max-h-[2000px] opacity-100"
      leave-to-class="max-h-0 opacity-0"
    >
      <div v-show="isExpanded" class="overflow-hidden">
        <div class="p-6 space-y-6">
      <!-- SSO Info Banner -->
      <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <svg class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div>
            <h4 class="text-sm font-semibold text-amber-900 dark:text-amber-300">⚠️ Security Warning</h4>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-400">
              Your password is managed by BangLipai Secure Portal. Changing your password will affect access to all integrated services.
            </p>
          </div>
        </div>
      </div>

      <!-- Change Password Section -->
      <div class="space-y-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Change Password</h3>

        <form @submit.prevent="changePassword" class="space-y-4">
          <!-- Current Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Current Password
            </label>
            <PasswordInput
              v-model="form.currentPassword"
              :error="form.errors.currentPassword"
              placeholder="Enter your current password"
              :show-strength="false"
            />
          </div>

          <!-- New Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              New Password
            </label>
            <PasswordInput
              v-model="form.newPassword"
              :error="form.errors.newPassword"
              placeholder="Enter your new password"
            />
          </div>

          <!-- Confirm New Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Confirm New Password
            </label>
            <PasswordInput
              v-model="form.confirmPassword"
              :error="form.errors.confirmPassword"
              placeholder="Confirm your new password"
              :show-strength="false"
            />
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 pt-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed transition-all shadow-lg shadow-blue-500/30 dark:shadow-blue-500/20"
            >
              <svg v-if="form.processing" class="h-5 w-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
              </svg>
              <span>{{ form.processing ? 'Changing Password...' : 'Change Password' }}</span>
            </button>

            <button
              v-if="hasChanges"
              type="button"
              @click="resetForm"
              :disabled="form.processing"
              class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-60 disabled:cursor-not-allowed transition-all"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>

      <!-- Password Change History -->
      <div v-if="passwordChangeLogs && passwordChangeLogs.length > 0" class="pt-6 border-t border-gray-200 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Password Changes</h3>

        <div class="space-y-3">
          <div
            v-for="log in passwordChangeLogs"
            :key="log.id"
            class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700"
          >
            <!-- Icon -->
            <div class="mt-0.5">
              <svg
                v-if="log.change_type === 'self_change'"
                class="h-5 w-5 text-blue-600 dark:text-blue-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <svg
                v-else-if="log.change_type === 'admin_reset'"
                class="h-5 w-5 text-orange-600 dark:text-orange-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ log.change_type === 'self_change' ? 'Self Password Change' : 'Admin Password Reset' }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ log.created_at }}
                  </p>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                  {{ log.created_at_human }}
                </span>
              </div>

              <!-- Admin reset details -->
              <div v-if="log.change_type === 'admin_reset' && log.changed_by" class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                  <span class="font-medium">Reset by:</span> {{ log.changed_by.name }} ({{ log.changed_by.email }})
                </p>
                <p v-if="log.reason" class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                  <span class="font-medium">Reason:</span> {{ log.reason }}
                </p>
              </div>

              <!-- IP Address -->
              <p v-if="log.ip_address" class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                IP: {{ log.ip_address }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- No History -->
      <div v-else class="pt-6 border-t border-gray-200 dark:border-gray-800">
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
          No password change history yet
        </p>
      </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import type { User } from '@/types/auth'
import PasswordInput from '@/components/PasswordInput.vue'

interface Props {
  user: User
  passwordChangeLogs?: any[]
}

defineProps<Props>()

// Accordion state - default expanded
const isExpanded = ref(true)

const toggleAccordion = () => {
  isExpanded.value = !isExpanded.value
}

const form = useForm({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const hasChanges = computed(() => {
  return form.currentPassword !== '' || form.newPassword !== '' || form.confirmPassword !== ''
})

const changePassword = () => {
  form.post('/profile/password/change', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    },
  })
}

const resetForm = () => {
  form.reset()
  form.clearErrors()
}
</script>
