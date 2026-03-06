<template>
  <AppLayout>
    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="p-6">
      <FormSkeleton :fieldCount="6" />
    </div>

    <!-- Form Content -->
    <div v-else class="space-y-6 p-6">
      <!-- Header with Back Button -->
      <div class="max-w-2xl mx-auto flex items-center gap-4">
        <Link
          href="/users"
          class="inline-flex items-center justify-center rounded-lg border border-gray-300 p-2 text-gray-700 transition-all hover:bg-gray-50 hover:border-gray-400 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:border-gray-500"
          title="Back to users"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </Link>
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Edit User</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Update informasi user</p>
        </div>
      </div>

      <!-- Form -->
      <div class="mx-auto max-w-2xl rounded-xl border border-gray-200 bg-white p-8 shadow-lg dark:border-gray-800 dark:bg-gray-900">
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Personal Information Section -->
          <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Personal Information
            </h2>

            <!-- Name -->
            <FormInput
              v-model="form.name"
              label="Full Name"
              placeholder="John Doe"
              :disabled="saving"
              :required="true"
              :error="localErrors.name || errors.name"
            />

            <!-- Email (Read-only) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
              <div class="relative">
                <input
                  :value="form.email"
                  type="email"
                  disabled
                  class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 pr-10 text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                />
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Email tidak bisa diubah
              </p>
            </div>
          </div>

          <!-- Contact & Additional Info Section -->
          <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Contact & Additional Info
            </h2>

            <!-- Phone -->
            <FormInput
              v-model="form.phone"
              label="Phone"
              type="tel"
              placeholder="+62 812 3456 7890"
              :disabled="saving"
              :error="errors.phone"
              helper-text="Format: +62 xxx xxx xxx"
            />

            <!-- Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
              <textarea
                v-model="form.address"
                :disabled="saving"
                rows="3"
                placeholder="Jl. Contoh No. 123, Jakarta"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
              ></textarea>
              <p v-if="errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.address }}</p>
            </div>

            <!-- Avatar -->
            <FormInput
              v-model="form.avatar"
              label="Avatar URL"
              type="url"
              placeholder="https://example.com/avatar.jpg"
              :disabled="saving"
              :error="localErrors.avatar || errors.avatar"
              helper-text="URL gambar profil pengguna"
            />
          </div>

          <!-- Buttons -->
          <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button
              type="submit"
              :disabled="saving || !isFormValid"
              class="group relative flex-1 inline-flex items-center justify-center gap-2 overflow-hidden rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 font-medium text-white transition-all hover:from-blue-700 hover:to-indigo-700 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:from-blue-600 disabled:hover:to-indigo-600 shadow-lg shadow-blue-500/30 dark:shadow-blue-500/20"
            >
              <svg
                v-if="saving"
                class="h-5 w-5 animate-spin"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ saving ? 'Saving Changes...' : 'Save Changes' }}</span>
            </button>
            <Link
              :href="`/users/${user.id}`"
              class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-6 py-3 font-medium text-gray-700 transition-all hover:bg-gray-50 hover:border-gray-400 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:border-gray-500"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Cancel
            </Link>
          </div>
        </form>
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
import { ref, computed, watch } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { TransitionGroup } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import FormSkeleton from '@/components/FormSkeleton.vue'
import FormInput from '@/components/FormInput.vue'
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'
import type { User } from '@/types/auth'

const page = usePage()
const { toasts, success, error } = useToast()

const saving = ref(false)
const errors = ref<Record<string, string>>({})

// Get user from page props
const user = computed(() => page.props.user as User)
const auth = computed(() => page.props.auth)

// Check if page is loading
const isLoading = computed(() => !user.value || !user.value.id)

const form = useForm({
  name: user.value?.name || '',
  email: user.value?.email || '',
  phone: user.value?.phone || '',
  address: user.value?.address || '',
  avatar: user.value?.avatar || '',
})

// Local validation errors (real-time)
const localErrors = computed(() => {
  const errs: Record<string, string> = {}

  // Name validation
  if (!form.name) {
    errs.name = 'Nama wajib diisi'
  } else if (form.name.length < 3) {
    errs.name = 'Nama minimal 3 karakter'
  }

  // URL validation - only if avatar is provided
  if (form.avatar && form.avatar.trim()) {
    try {
      new URL(form.avatar)
    } catch {
      errs.avatar = 'Format URL tidak valid'
    }
  }

  return errs
})

// Form validation
const isFormValid = computed(() => {
  return form.name &&
         form.name.length >= 3 &&
         Object.keys(localErrors.value).length === 0
})

// Update form when user changes
watch(
  () => user.value,
  (newUser) => {
    if (newUser) {
      form.name = newUser.name
      form.email = newUser.email
      form.phone = newUser.phone || ''
      form.address = newUser.address || ''
      form.avatar = newUser.avatar || ''
    }
  },
  { deep: true }
)

// Watch for flash messages from backend
watch(
  () => ({
    flash: page.props.flash,
  }),
  (newValue) => {
    const flash = newValue.flash as any
    if (flash?.success) {
      success(flash.success, 'User Updated')
    }
    if (flash?.error) {
      error(flash.error, 'Error')
    }
  }
)

const submitForm = async () => {
  // Check client-side validation first
  if (!isFormValid.value) {
    error('Mohon lengkapi semua field yang wajib diisi dengan benar', 'Validation Error')
    return
  }

  // Reset errors before submit
  errors.value = {}
  saving.value = true

  try {
    form.patch(`/users/${user.value.id}`, {
      onError: (err: any) => {
        errors.value = err
        if (Object.keys(err).length > 0) {
          error('Mohon periksa kembali form Anda', 'Validation Error')
        }
      },
      onSuccess: () => {
        success('User berhasil diperbarui', 'Success!')
        // Redirect after showing toast
        setTimeout(() => {
          window.location.href = `/users/${user.value.id}`
        }, 1500)
      },
      onFinish: () => {
        saving.value = false
      },
    })
  } catch (err) {
    error('Terjadi kesalahan saat memperbarui user', 'Error')
    saving.value = false
  }
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
</style>
