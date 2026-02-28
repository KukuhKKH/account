<template>
    <div
        class="pointer-events-none fixed top-0 right-0 z-50 flex max-h-screen flex-col gap-3 p-4 md:p-6"
    >
        <TransitionGroup name="toast" tag="div" class="flex flex-col gap-3">
            <Toast
                v-for="toast in toastItems"
                :key="toast.id"
                :type="toast.type"
                :message="toast.message"
                :title="toast.title"
                :duration="toast.duration"
            />
        </TransitionGroup>
    </div>
</template>

<script setup lang="ts">
import { watch, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Toast from './Toast.vue';
import { useToast } from '../composables/useToast';

const page = usePage();
const { toasts: toastItems, clear, success, error, warning, info } = useToast();
const lastFlashId = ref<string>('');

watch(
    () => page.props.flash,
    (flash: any) => {
        if (!flash) return;

        const hasValidMessage = Object.values(flash).some(
            (val) => typeof val === 'string' && val.trim() !== '',
        );

        if (!hasValidMessage) {
            clear();
            return;
        }

        const flashId = JSON.stringify(flash);
        if (flashId === lastFlashId.value) return;
        lastFlashId.value = flashId;

        clear();
        if (flash.success?.length > 0) {
            success(flash.success);
        }

        if (flash.error?.length > 0) {
            error(flash.error);
        }

        if (flash.warning?.length > 0) {
            warning(flash.warning);
        }

        if (flash.info?.length > 0) {
            info(flash.info);
        }
    },
    { immediate: true },
);
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition:
        transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
        opacity 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(40px) scale(0.96);
}

.toast-enter-to {
    opacity: 1;
    transform: translateX(0) scale(1);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(40px) scale(0.98);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>
