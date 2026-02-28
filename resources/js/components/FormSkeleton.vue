<template>
  <div class="space-y-6">
    <!-- Header Skeleton -->
    <div>
      <div class="h-8 w-48 animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800"></div>
      <div class="mt-4 h-4 w-96 animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800"></div>
    </div>

    <!-- Form Skeleton -->
    <div class="mx-auto max-w-2xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
      <div class="space-y-6">
        <!-- Field Group (repeated) -->
        <div v-for="i in fieldCount" :key="i">
          <!-- Label -->
          <div class="h-4 w-32 animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800"></div>
          <!-- Input -->
          <div class="mt-3 h-10 w-full animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800"></div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-4 pt-4">
          <div class="flex-1">
            <div class="h-10 w-full animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800"></div>
          </div>
          <div class="w-24">
            <div class="h-10 w-full animate-pulse rounded-lg bg-gray-200 dark:bg-gray-800"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  fieldCount?: number
}

withDefaults(defineProps<Props>(), {
  fieldCount: 6,
})
</script>
