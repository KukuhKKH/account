<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 py-8">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with Back Button -->
        <div class="mb-8 flex items-center gap-4">
          <Link
            :href="`/users/${user.id}`"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 p-2 text-gray-700 transition-all hover:bg-gray-50 hover:border-gray-400 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:border-gray-500"
            title="Back to user details"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </Link>
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reset Password</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Reset password untuk: <span class="font-medium">{{ user.name }}</span></p>
          </div>
        </div>

        <!-- Warning Banner -->
        <div class="mb-6 bg-orange-50 dark:bg-orange-900/20 border-l-4 border-orange-500 p-4 rounded-r-lg">
          <div class="flex items-start gap-3">
            <svg class="h-6 w-6 text-orange-600 dark:text-orange-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-orange-900 dark:text-orange-300">⚠️ Administrator Action</h3>
              <div class="mt-2 text-sm text-orange-800 dark:text-orange-400 space-y-1">
                <p>• Password akan langsung diubah melalui BangLipai Secure Portal</p>
                <p>• User akan menerima notifikasi perubahan password</p>
                <p>• Aktivitas ini akan dicatat dalam log dengan identitas Anda</p>
                <p>• Alasan reset password wajib diisi untuk audit trail</p>
              </div>
            </div>
          </div>
        </div>

        <!-- User Info Card -->
        <div class="mb-6 bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 p-6">
          <div class="flex items-center gap-4">
            <div class="relative">
              <img
                v-if="user.avatar"
                :src="user.avatar"
                :alt="user.name"
                class="h-16 w-16 rounded-full object-cover ring-4 ring-blue-100 dark:ring-blue-900/30"
              />
              <div
                v-else
                class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white text-xl font-bold ring-4 ring-blue-100 dark:ring-blue-900/30"
              >
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
              <div class="flex gap-2 mt-2">
                <span
                  v-for="(role, index) in user.roles"
                  :key="index"
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400"
                >
                  {{ role }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Reset Password Form -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
          <div class="p-6 border-b border-gray-200 dark:border-gray-800 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Set New Password
            </h2>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-6">
            <!-- Reason -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Alasan Reset Password <span class="text-red-600">*</span>
              </label>
              <textarea
                v-model="form.reason"
                :disabled="saving"
                rows="3"
                required
                placeholder="Contoh: User lupa password dan request reset melalui email support"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
              ></textarea>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Alasan ini akan dicatat dalam log dan dapat dilihat oleh superadmin
              </p>
              <p v-if="errors.reason" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ errors.reason }}
              </p>
            </div>

            <!-- Password -->
            <PasswordInput
              v-model="form.password"
              label="New Password"
              :disabled="saving"
              :required="true"
              :error="localErrors.password || errors.password"
              :show-strength="true"
              :show-requirements="true"
            />

            <!-- Password Confirmation -->
            <PasswordInput
              v-model="form.password_confirmation"
              label="Confirm New Password"
              :disabled="saving"
              :required="true"
              :error="localErrors.password_confirmation || errors.password_confirmation"
              :show-strength="false"
              :show-requirements="false"
            />

            <!-- Password Match Indicator -->
            <Transition name="slide-down">
              <div
                v-if="form.password && form.password_confirmation"
                :class="[
                  'flex items-center gap-2 text-sm font-medium p-3 rounded-lg',
                  passwordsMatch
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                ]"
              >
                <svg v-if="passwordsMatch" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ passwordsMatch ? 'Password cocok!' : 'Password tidak cocok' }}</span>
              </div>
            </Transition>

            <!-- Confirmation Checkbox -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-800">
              <label class="flex items-start gap-3 cursor-pointer">
                <input
                  v-model="confirmReset"
                  type="checkbox"
                  required
                  class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">
                  Saya memahami bahwa password user akan langsung diubah dan aktivitas ini akan tercatat dalam sistem log.
                </span>
              </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-800">
              <button
                type="submit"
                :disabled="saving || !isFormValid || !confirmReset"
                class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-red-600 to-orange-600 text-white font-medium rounded-lg hover:from-red-700 hover:to-orange-700 disabled:opacity-60 disabled:cursor-not-allowed transition-all shadow-lg shadow-red-500/30 dark:shadow-red-500/20"
              >
                <svg v-if="saving" class="h-5 w-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>{{ saving ? 'Resetting Password...' : 'Reset Password' }}</span>
              </button>
              <Link
                :href="`/users/${user.id}`"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:border-gray-400 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:border-gray-500"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel
              </Link>
            </div>
          </form>
        </div>

        <!-- Password Change History -->
        <div v-if="passwordChangeLogs && passwordChangeLogs.length > 0" class="mt-6 bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
          <div class="p-6 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Password Change History
            </h2>
          </div>

          <div class="divide-y divide-gray-200 dark:divide-gray-800">
            <div
              v-for="log in passwordChangeLogs"
              :key="log.id"
              class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50"
            >
              <div class="flex items-start gap-3">
                <svg
                  v-if="log.change_type === 'admin_reset'"
                  class="h-5 w-5 text-orange-600 dark:text-orange-400 mt-0.5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <svg
                  v-else
                  class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>

                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ log.change_type === 'admin_reset' ? 'Admin Password Reset' : 'Self Password Change' }}
                  </p>
                  <p v-if="log.changed_by" class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    By: {{ log.changed_by.name }} ({{ log.changed_by.email }})
                  </p>
                  <p v-if="log.reason" class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Reason: {{ log.reason }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    {{ log.created_at }} • {{ log.ip_address }}
                  </p>
                </div>

                <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                  {{ log.created_at_human }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <TransitionGroup name="toast-list">
      <Toast
        v-for="toast in toasts"
        :key="toast.id"
        :type="toast.type"
        :message="toast.message"
        :title="toast.title"
        :duration="toast.duration"
      />
    </TransitionGroup>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { TransitionGroup } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'
import type { User } from '@/types/auth'

const page = usePage()
const { toasts, success, error } = useToast()

const user = computed(() => page.props.user as User)
const passwordChangeLogs = computed(() => page.props.passwordChangeLogs as any[])

const form = ref({
  password: '',
  password_confirmation: '',
  reason: '',
})

const confirmReset = ref(false)
const saving = ref(false)
const errors = ref<Record<string, string>>({})

// Local validation
const localErrors = computed(() => {
  const errs: Record<string, string> = {}

  if (form.value.password && form.value.password.length < 8) {
    errs.password = 'Password minimal 8 karakter'
  }

  if (form.value.password && form.value.password_confirmation && form.value.password !== form.value.password_confirmation) {
    errs.password_confirmation = 'Password tidak cocok'
  }

  return errs
})

const passwordsMatch = computed(() => {
  return form.value.password && form.value.password_confirmation && form.value.password === form.value.password_confirmation
})

const isFormValid = computed(() => {
  return (
    form.value.password &&
    form.value.password_confirmation &&
    form.value.reason &&
    form.value.password === form.value.password_confirmation &&
    form.value.password.length >= 8 &&
    Object.keys(localErrors.value).length === 0
  )
})

const submitForm = () => {
  if (!isFormValid.value || !confirmReset.value) {
    error('Mohon lengkapi semua field yang diperlukan', 'Validation Error')
    return
  }

  errors.value = {}
  saving.value = true

  router.post(
    `/users/${user.value.id}/reset-password`,
    form.value,
    {
      onError: (err: any) => {
        errors.value = err
        if (Object.keys(err).length > 0) {
          error('Mohon periksa kembali form Anda', 'Validation Error')
        }
      },
      onSuccess: () => {
        success('Password berhasil direset. User dapat login dengan password baru.', 'Success!')
      },
      onFinish: () => {
        saving.value = false
      },
    }
  )
}
</script>

<style scoped>
.toast-list-enter-active,
.toast-list-leave-active {
  transition: all 0.3s ease;
}

.toast-list-enter-from,
.toast-list-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.slide-down-enter-active {
  transition: all 0.2s ease;
}

.slide-down-leave-active {
  transition: all 0.15s ease;
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-4px);
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-2px);
}
</style>
