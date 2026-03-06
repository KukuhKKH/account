<template>
  <div class="space-y-1">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
      <span v-if="required" class="text-red-600">*</span>
    </label>

    <div class="relative">
      <input
        :value="modelValue"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="[
          'w-full rounded-lg border px-4 py-2.5 pr-10 transition-all duration-200 focus:outline-none',
          inputClasses,
          {
            'border-red-300 bg-red-50/50 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/10': showError,
            'border-green-300 bg-green-50/50 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-green-600 dark:bg-green-900/10': showSuccess,
            'border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white': !showError && !showSuccess,
            'bg-gray-50 cursor-not-allowed dark:bg-gray-700': disabled
          }
        ]"
      />

      <!-- Validation Icon -->
      <div class="absolute right-3 top-1/2 -translate-y-1/2">
        <Transition name="fade">
          <svg
            v-if="showSuccess"
            class="h-5 w-5 text-green-600 dark:text-green-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <svg
            v-else-if="showError"
            class="h-5 w-5 text-red-600 dark:text-red-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </Transition>
      </div>
    </div>

    <!-- Helper Text or Error -->
    <Transition name="slide-down">
      <p v-if="error && touched" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ error }}
      </p>
      <p v-else-if="helperText" class="text-xs text-gray-500 dark:text-gray-400">
        {{ helperText }}
      </p>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

interface Props {
  modelValue: string | number
  label?: string
  type?: string
  placeholder?: string
  disabled?: boolean
  required?: boolean
  error?: string
  helperText?: string
  validateOnInput?: boolean
  inputClasses?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  placeholder: '',
  disabled: false,
  required: false,
  validateOnInput: true
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const touched = ref(false)
const focused = ref(false)

const showError = computed(() => {
  return touched.value && props.error && !focused.value
})

const showSuccess = computed(() => {
  return touched.value && !props.error && props.modelValue && String(props.modelValue).length > 0 && !focused.value
})

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)

  if (props.validateOnInput) {
    touched.value = true
  }
}

const handleBlur = (event: Event) => {
  touched.value = true
  focused.value = false
  emit('blur', event)
}

const handleFocus = (event: Event) => {
  focused.value = true
  emit('focus', event)
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
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
