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
        :type="showPassword ? 'text' : 'password'"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="[
          'w-full rounded-lg border px-4 py-2.5 pr-20 transition-all duration-200 focus:outline-none',
          {
            'border-red-300 bg-red-50/50 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/10': showError,
            'border-green-300 bg-green-50/50 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-green-600 dark:bg-green-900/10': showSuccess,
            'border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white': !showError && !showSuccess,
            'bg-gray-50 cursor-not-allowed dark:bg-gray-700': disabled
          }
        ]"
      />

      <!-- Eye Icon & Validation Icon -->
      <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
        <!-- Validation Icon -->
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

        <!-- Toggle Password Visibility -->
        <button
          v-if="modelValue"
          type="button"
          @click="showPassword = !showPassword"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          tabindex="-1"
        >
          <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
          </svg>
          <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Password Strength Meter -->
    <Transition name="slide-down">
      <div v-if="showStrength && modelValue && String(modelValue).length > 0" class="space-y-2 mt-2">
        <div class="flex items-center gap-2">
          <div class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div
              :class="[
                'h-full transition-all duration-300 rounded-full',
                {
                  'bg-red-500': strength.color === 'red',
                  'bg-yellow-500': strength.color === 'yellow',
                  'bg-green-500': strength.color === 'green'
                }
              ]"
              :style="{ width: `${(strength.score / 6) * 100}%` }"
            ></div>
          </div>
          <span
            :class="[
              'text-xs font-medium',
              {
                'text-red-600 dark:text-red-400': strength.color === 'red',
                'text-yellow-600 dark:text-yellow-400': strength.color === 'yellow',
                'text-green-600 dark:text-green-400': strength.color === 'green'
              }
            ]"
          >
            {{ strength.label }}
          </span>
        </div>

        <!-- Requirements -->
        <div v-if="showRequirements" class="grid grid-cols-2 gap-1 text-xs">
          <div
            v-for="(req, index) in requirements"
            :key="index"
            :class="[
              'flex items-center gap-1',
              req.met ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500'
            ]"
          >
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                v-if="req.met"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
              <circle v-else cx="12" cy="12" r="10" stroke-width="2" opacity="0.3" />
            </svg>
            <span>{{ req.label }}</span>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Helper Text or Error -->
    <Transition name="slide-down">
      <p v-if="error && touched" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ error }}
      </p>
      <p v-else-if="helperText && !showStrength" class="text-xs text-gray-500 dark:text-gray-400">
        {{ helperText }}
      </p>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePasswordStrength } from '@/composables/useFormValidation'

interface Props {
  modelValue: string
  label?: string
  placeholder?: string
  disabled?: boolean
  required?: boolean
  error?: string
  helperText?: string
  showStrength?: boolean
  showRequirements?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: '••••••••',
  disabled: false,
  required: false,
  showStrength: true,
  showRequirements: true
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const touched = ref(false)
const focused = ref(false)
const showPassword = ref(false)

const { getPasswordStrength, getPasswordRequirements } = usePasswordStrength()

const strength = computed(() => getPasswordStrength(props.modelValue))
const requirements = computed(() => getPasswordRequirements(props.modelValue))

const showError = computed(() => {
  return touched.value && props.error && !focused.value
})

const showSuccess = computed(() => {
  return touched.value && !props.error && props.modelValue && props.modelValue.length >= 8 && !focused.value
})

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
  touched.value = true
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
