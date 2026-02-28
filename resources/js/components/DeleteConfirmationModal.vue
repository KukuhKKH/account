<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <!-- Click outside to close -->
        <div class="absolute inset-0" @click="onCancel"></div>

        <!-- Modal Content -->
        <div
          class="relative z-10 flex w-full max-w-sm flex-col gap-6 rounded-lg border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-800 dark:bg-gray-900"
          @click.stop
        >
          <!-- Icon -->
          <div class="flex justify-center">
            <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/30">
              <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
          </div>

          <!-- Content -->
          <div class="text-center">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ description }}</p>
          </div>

          <!-- Actions -->
          <div class="flex gap-3">
            <button
              @click="onCancel"
              :disabled="isLoading"
              class="flex-1 rounded-lg border border-gray-300 px-4 py-2 font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
            >
              Cancel
            </button>
            <button
              @click="onConfirm"
              :disabled="isLoading"
              class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 font-medium text-white transition-colors hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-red-700 dark:hover:bg-red-800"
            >
              <svg
                v-if="isLoading"
                class="h-4 w-4 animate-spin"
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
              <span>{{ isLoading ? 'Deleting...' : 'Delete' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
interface Props {
  open: boolean
  title?: string
  description?: string
  isLoading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Delete User',
  description: 'Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.',
  isLoading: false,
})

const emit = defineEmits<{
  confirm: []
  cancel: []
}>()

const onConfirm = () => {
  emit('confirm')
}

const onCancel = () => {
  emit('cancel')
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active > div:nth-child(2),
.modal-leave-active > div:nth-child(2) {
  transition: all 0.3s ease;
}

.modal-enter-from > div:nth-child(2),
.modal-leave-to > div:nth-child(2) {
  opacity: 0;
  transform: scale(0.95);
}
</style>
