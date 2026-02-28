<template>
  <Teleport to="body">
    <Transition name="toast-group">
      <div
        v-if="visible"
        :class="[
          'fixed right-4 top-4 z-50 flex max-w-sm gap-3 rounded-lg border px-4 py-3 shadow-lg backdrop-blur-sm md:right-6 md:top-6',
          variantClasses
        ]"
        role="alert"
      >
        <!-- Icon -->
        <div :class="['shrink-0', iconColorClass]">
          <svg v-if="type === 'success'" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            />
          </svg>
          <svg v-else-if="type === 'error'" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            />
          </svg>
          <svg v-else-if="type === 'warning'" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
              clip-rule="evenodd"
            />
          </svg>
          <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0zM8 9a1 1 0 100-2 1 1 0 000 2zm5 0a1 1 0 100-2 1 1 0 000 2z"
              clip-rule="evenodd"
            />
          </svg>
        </div>

        <!-- Content -->
        <div class="flex-1">
          <p v-if="title" :class="['font-semibold', textColorClass]">{{ title }}</p>
          <p :class="['text-sm', textColorClass, title ? 'mt-1' : '']">{{ message }}</p>
        </div>

        <!-- Close Button -->
        <button
          @click="close"
          :class="['shrink-0 rounded-md p-1 transition-colors hover:bg-white/20', textColorClass]"
          aria-label="Close notification"
        >
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd"
            />
          </svg>
        </button>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

interface Props {
  type?: 'success' | 'error' | 'warning' | 'info'
  message: string
  title?: string
  duration?: number
}

const props = withDefaults(defineProps<Props>(), {
  type: 'info',
  duration: 5000,
})

const visible = ref(true)

const variantClasses = computed(() => {
  const baseClasses = 'border'
  switch (props.type) {
    case 'success':
      return `${baseClasses} border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/50`
    case 'error':
      return `${baseClasses} border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/50`
    case 'warning':
      return `${baseClasses} border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/50`
    default:
      return `${baseClasses} border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/50`
  }
})

const textColorClass = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-emerald-900 dark:text-emerald-200'
    case 'error':
      return 'text-red-900 dark:text-red-200'
    case 'warning':
      return 'text-amber-900 dark:text-amber-200'
    default:
      return 'text-blue-900 dark:text-blue-200'
  }
})

const iconColorClass = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-emerald-600 dark:text-emerald-400'
    case 'error':
      return 'text-red-600 dark:text-red-400'
    case 'warning':
      return 'text-amber-600 dark:text-amber-400'
    default:
      return 'text-blue-600 dark:text-blue-400'
  }
})

const close = () => {
  visible.value = false
}

onMounted(() => {
  if (props.duration && props.duration > 0) {
    setTimeout(() => {
      close()
    }, props.duration)
  }
})
</script>

<style scoped>
.toast-group-enter-active,
.toast-group-leave-active {
  transition: all 0.3s ease;
}

.toast-group-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-group-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
