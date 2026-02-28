<template>
  <AppLayout>
    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="p-6">
      <FormSkeleton :fieldCount="7" />
    </div>

    <!-- Form Content -->
    <div v-else class="space-y-6 p-6">
      <!-- Header -->
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Create User</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Tambah user baru ke sistem</p>
      </div>

      <!-- Form -->
      <div class="mx-auto max-w-2xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Full Name <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              :disabled="saving"
              placeholder="John Doe"
              class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 transition-colors focus:border-blue-500 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Email <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              :disabled="saving"
              placeholder="john@example.com"
              class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 transition-colors focus:border-blue-500 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Password <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.password"
              type="password"
              required
              :disabled="saving"
              minlength="8"
              class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 transition-colors focus:border-blue-500 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimal 8 karakter</p>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <!-- Role -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Role <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.role"
              required
              :disabled="saving"
              class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 transition-colors focus:border-blue-500 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
            >
              <option value="">Select Role</option>
              <option value="user">User</option>
              <option value="admin">Admin</option>
              <option value="superadmin">Superadmin</option>
            </select>
            <p v-if="errors.role" class="mt-1 text-sm text-red-600">{{ errors.role }}</p>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
            <input
              v-model="form.phone"
              type="tel"
              :disabled="saving"
              placeholder="+62 812 3456 7890"
              class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 transition-colors focus:border-blue-500 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
            />
            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
            <textarea
              v-model="form.address"
              :disabled="saving"
              rows="3"
              placeholder="Jl. Contoh No. 123"
              class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 transition-colors focus:border-blue-500 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
            ></textarea>
            <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
          </div>

          <!-- Avatar -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Avatar URL</label>
            <input
              v-model="form.avatar"
              type="url"
              :disabled="saving"
              placeholder="https://example.com/avatar.jpg"
              class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 transition-colors focus:border-blue-500 focus:outline-none disabled:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700"
            />
            <p v-if="errors.avatar" class="mt-1 text-sm text-red-600">{{ errors.avatar }}</p>
          </div>

          <!-- Buttons -->
          <div class="flex gap-4 pt-4">
            <button
              type="submit"
              :disabled="saving"
              class="group relative flex-1 inline-flex items-center justify-center gap-2 overflow-hidden rounded-lg bg-blue-600 px-6 py-2 font-medium text-white transition-all hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-600 disabled:opacity-75 dark:bg-blue-500 dark:hover:bg-blue-600"
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
              <span>{{ saving ? 'Creating...' : 'Create User' }}</span>
            </button>
            <Link
              href="/users"
              class="rounded-lg border border-gray-300 px-6 py-2 font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
            >
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
import Toast from '@/components/Toast.vue'
import { useToast } from '@/composables/useToast'

const page = usePage()
const { toasts, success, error } = useToast()
const saving = ref(false)
const errors = ref<Record<string, string>>({})

// Check if page is loading
const isLoading = computed(() => page.component === 'Users/Create' && !Object.keys(page.props).length)

const form = useForm({
  name: '',
  email: '',
  password: '',
  role: '',
  phone: '',
  address: '',
  avatar: '',
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
</style>
      },
      onSuccess: () => {
      },
    })
  } finally {
    saving.value = false
  }
}
</script>
