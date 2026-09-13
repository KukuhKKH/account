<script setup lang="ts">
import { computed, ref } from 'vue'
import { useAvatar } from '~/composables/useAvatar'
import type { UserRoleType } from '~/types/auth'

const props = withDefaults(
  defineProps<{
    name?: string
    email?: string
    avatar?: string | null
    role?: UserRoleType | string
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl'
    shape?: 'rounded' | 'circle'
    customClass?: string
  }>(),
  {
    name: 'User',
    email: '',
    avatar: null,
    role: 'User',
    size: 'md',
    shape: 'rounded',
    customClass: ''
  }
)

const { getAvatarUrl } = useAvatar()
const imageLoadError = ref(false)

const avatarSrc = computed(() => {
  return getAvatarUrl({
    name: props.name,
    email: props.email,
    avatar: props.avatar
  })
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'xs':
      return 'w-6 h-6 text-[10px]'
    case 'sm':
      return 'w-8 h-8 text-xs'
    case 'lg':
      return 'w-12 h-12 text-base'
    case 'xl':
      return 'w-16 h-16 text-xl'
    case 'md':
    default:
      return 'w-10 h-10 text-sm'
  }
})

const shapeClasses = computed(() => {
  if (props.shape === 'circle') return 'rounded-full'
  switch (props.size) {
    case 'xs':
      return 'rounded-lg'
    case 'sm':
      return 'rounded-xl'
    case 'xl':
      return 'rounded-3xl'
    case 'lg':
    case 'md':
    default:
      return 'rounded-2xl'
  }
})

const roleGradient = computed(() => {
  switch (props.role) {
    case 'Superadmin':
      return 'from-rose-500 to-pink-600 shadow-rose-500/20'
    case 'Admin Account':
    case 'Admin':
      return 'from-sky-500 to-indigo-600 shadow-sky-500/20'
    default:
      return 'from-indigo-500 to-violet-600 shadow-indigo-500/20'
  }
})

const initial = computed(() => {
  return props.name ? props.name.charAt(0).toUpperCase() : 'U'
})
</script>

<template>
  <div
    class="relative shrink-0 flex items-center justify-center overflow-hidden font-bold transition-all"
    :class="[sizeClasses, shapeClasses, customClass]"
  >
    <!-- Avatar Image (Custom URL or Fallback to https://avatar.banglipai.web.id/) -->
    <img
      v-if="!imageLoadError"
      :src="avatarSrc"
      :alt="props.name || 'User Avatar'"
      @error="imageLoadError = true"
      class="w-full h-full object-cover"
      :class="shapeClasses"
      loading="lazy"
    />

    <!-- Fallback Gradient Initial jika gambar offline / error -->
    <div
      v-else
      class="w-full h-full bg-gradient-to-tr text-white flex items-center justify-center font-black shadow-inner select-none"
      :class="[roleGradient, shapeClasses]"
    >
      {{ initial }}
    </div>
  </div>
</template>
