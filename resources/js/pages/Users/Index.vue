<template>
  <AppLayout>
    <div class="space-y-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Users</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Manage users dalam sistem</p>
        </div>
        <Link
          href="/users/create"
          class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2 font-medium text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
        >
          <Plus class="-ml-1 mr-2 h-5 w-5" />
          New User
        </Link>
      </div>

      <!-- Filters & Search -->
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <form @submit.prevent="applyFilters" class="space-y-4">
          <div class="grid gap-4 md:grid-cols-3">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Name or email..."
                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
              />
            </div>

            <!-- Role Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
              <select
                v-model="filters.role"
                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
              >
                <option value="">All Roles</option>
                <option value="superadmin">Superadmin</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
              </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
              <button
                type="submit"
                class="flex-1 rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
              >
                Filter
              </button>
              <button
                type="button"
                @click="resetFilters"
                class="rounded-lg border border-gray-300 px-4 py-2 font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
              >
                Reset
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Users Table -->
      <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-800">
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Name</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Email</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Role</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Last Login</th>
                <th class="px-6 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="user in users.data"
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
                    <div v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-xs font-medium text-white">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                    :class="{
                      'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': user.role === 'superadmin',
                      'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': user.role === 'admin',
                      'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': user.role === 'user'
                    }"
                  >
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                  {{ formatDate(user.last_login_at) }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex justify-end gap-1">
                    <!-- View Button -->
                    <Link
                      :href="`/users/${user.id}`"
                      class="inline-flex items-center justify-center rounded-lg border border-gray-300 p-2 text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                      title="View user details"
                    >
                      <Eye class="h-4 w-4" />
                    </Link>

                    <!-- Edit Button -->
                    <Link
                      :href="`/users/${user.id}/edit`"
                      class="inline-flex items-center justify-center rounded-lg border border-gray-300 p-2 text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                      title="Edit user"
                    >
                      <Pencil class="h-4 w-4" />
                    </Link>

                    <!-- Delete Button -->
                    <button
                      @click="openDeleteModal(user)"
                      :disabled="isDeleting || user.id === auth.user?.id"
                      class="inline-flex items-center justify-center rounded-lg border border-red-300 p-2 text-red-700 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-600 dark:text-red-400 dark:hover:bg-red-900/20"
                      :title="user.id === auth.user?.id ? 'Tidak bisa menghapus user Anda sendiri' : 'Delete user'"
                    >
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="users.data.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
            No users found
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="users.links.length > 1" class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Showing <span class="font-medium">{{ users.from }}</span> to
              <span class="font-medium">{{ users.to }}</span> of
              <span class="font-medium">{{ users.total }}</span> users
            </p>
            <div class="flex gap-2">
              <template v-for="link in users.links" :key="link.label">
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

    <!-- Delete Confirmation Modal -->
    <DeleteConfirmationModal
      :open="showDeleteModal"
      :title="`Delete User: ${userToDelete?.name}`"
      :description="`Apakah Anda yakin ingin menghapus user &quot;${userToDelete?.name}&quot;? Tindakan ini tidak dapat dibatalkan.`"
      :is-loading="isDeleting"
      @confirm="confirmDelete"
      @cancel="closeDeleteModal"
    />

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
import { Link, usePage, router } from '@inertiajs/vue3'
import { TransitionGroup } from 'vue'
import { Plus, Eye, Pencil, Trash2 } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import Toast from '@/components/Toast.vue'
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue'
import { useToast } from '@/composables/useToast'

const page = usePage()
const { toasts, success, error } = useToast()
const auth = computed(() => page.props.auth as any)
const users = computed(() => page.props.users as any)

// Modal state
const showDeleteModal = ref(false)
const userToDelete = ref<any>(null)
const isDeleting = ref(false)

const filters = ref({
  search: (page.props.filters?.search as string | undefined) || '',
  role: (page.props.filters?.role as string | undefined) || '',
})

// Watch for flash messages from backend
watch(
  () => ({
    flash: page.props.flash,
  }),
  (newValue) => {
    const flash = newValue.flash as any
    if (flash?.success) {
      success(flash.success)
    }
    if (flash?.error) {
      error(flash.error)
    }
  }
)

const applyFilters = () => {
  router.get('/users', filters.value, { preserveState: true })
}

const resetFilters = () => {
  filters.value = { search: '', role: '' }
  router.get('/users')
}

const openDeleteModal = (user: any) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  userToDelete.value = null
}

const confirmDelete = () => {
  if (!userToDelete.value) return

  isDeleting.value = true
  router.delete(`/users/${userToDelete.value.id}`, {
    onSuccess: () => {
      closeDeleteModal()
      success(`User "${userToDelete.value.name}" berhasil dihapus`, 'User Deleted')
    },
    onError: (errors) => {
      const errorMessage = Object.values(errors)[0] || 'Gagal menghapus user'
      error(errorMessage as string, 'Delete Failed')
    },
    onFinish: () => {
      isDeleting.value = false
    },
  })
}

const formatDate = (date: string | null | undefined) => {
  if (!date) return 'Tidak pernah'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
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
