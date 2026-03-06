<template>
  <AppLayout>
    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="p-6">
      <FormSkeleton :fieldCount="7" />
    </div>

    <!-- Form Content -->
    <div v-else class="space-y-6 p-6">
      <!-- Header -->
      <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Create User</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Tambah user baru ke sistem dengan informasi lengkap</p>
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

            <!-- Email -->
            <FormInput
              v-model="form.email"
              label="Email"
              type="email"
              placeholder="john@example.com"
              :disabled="saving"
              :required="true"
              :error="localErrors.email || errors.email"
            />
          </div>

          <!-- Security Section -->
          <div class="space-y-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 p-6 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-100 dark:border-blue-800/30">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Security Information
            </h2>

            <!-- Password -->
            <PasswordInput
              v-model="form.password"
              label="Password"
              :disabled="saving"
              :required="true"
              :error="localErrors.password || errors.password"
              :show-strength="true"
              :show-requirements="true"
            />

            <!-- Password Confirmation -->
            <PasswordInput
              v-model="form.password_confirmation"
              label="Confirm Password"
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
          </div>

          <!-- Role & Additional Info Section -->
          <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Role & Additional Info
            </h2>

            <!-- Role -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Role <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.role"
                :disabled="saving"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
              >
                <option value="">Select Role</option>
                <option :value="roles.USER">{{ roles.USER }}</option>
                <option :value="roles.ADMIN">{{ roles.ADMIN }}</option>
                <option v-if="isSuperadmin" :value="roles.SUPERADMIN">{{ roles.SUPERADMIN }}</option>
              </select>
              <p v-if="errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ errors.role }}
              </p>
            </div>

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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
              <span>{{ saving ? 'Creating User...' : 'Create User' }}</span>
            </button>
            <Link
              href="/users"
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
import PasswordInput from '@/components/PasswordInput.vue'
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'
import { useAuth } from '@/composables/useAuth'
import { validationRules } from '@/composables/useFormValidation'

const page = usePage()
const { toasts, success, error } = useToast()
const { isSuperadmin } = useAuth()
const roles = computed(() => page.props.roles as any)
const saving = ref(false)
const errors = ref<Record<string, string>>({})

// Check if page is loading
const isLoading = computed(() => page.component === 'Users/Create' && !Object.keys(page.props).length)

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
  phone: '',
  address: '',
  avatar: '',
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

  // Email validation
  if (!form.email) {
    errs.email = 'Email wajib diisi'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errs.email = 'Format email tidak valid'
  }

  // Password validation
  if (!form.password) {
    errs.password = 'Password wajib diisi'
  } else if (form.password.length < 8) {
    errs.password = 'Password minimal 8 karakter'
  }

  // Password confirmation
  if (form.password && form.password_confirmation && form.password !== form.password_confirmation) {
    errs.password_confirmation = 'Password tidak cocok'
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

// Password match checker
const passwordsMatch = computed(() => {
  return form.password && form.password_confirmation && form.password === form.password_confirmation
})

// Form validation
const isFormValid = computed(() => {
  return form.name &&
         form.email &&
         form.password &&
         form.password_confirmation &&
         form.role &&
         form.password === form.password_confirmation &&
         form.password.length >= 8 &&
         Object.keys(localErrors.value).length === 0
})

// Watch for flash messages from backend
watch(
  () => ({
    flash: page.props.flash,
  }),
  (newValue) => {
    const flash = newValue.flash as any
    if (flash?.success) {
      success(flash.success, 'User Created')
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
    form.post('/users', {
      onError: (err: any) => {
        errors.value = err
        if (Object.keys(err).length > 0) {
          error('Mohon periksa kembali form Anda', 'Validation Error')
        }
      },
      onSuccess: () => {
        form.reset()
        success('User berhasil dibuat', 'Success!')
        // Redirect after showing toast
        setTimeout(() => {
          window.location.href = '/users'
        }, 1500)
      },
      onFinish: () => {
        saving.value = false
      },
    })
  } catch (err) {
    error('Terjadi kesalahan saat membuat user', 'Error')
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
