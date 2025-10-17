<script setup lang="ts">
import { ref, computed } from 'vue';
import { AlertTriangle, CheckCircle, Info, XCircle, X } from 'lucide-vue-next';

export interface ConfirmDialogOptions {
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'warning' | 'info' | 'success';
}

const isOpen = ref(false);
const options = ref<ConfirmDialogOptions>({
    title: '',
    message: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'danger',
});

let resolvePromise: ((value: boolean) => void) | null = null;

const variantConfig = computed(() => {
    const configs = {
        danger: {
            icon: XCircle,
            iconClass: 'text-red-500',
            iconBg: 'bg-red-100 dark:bg-red-900/30',
            confirmClass: 'bg-red-600 hover:bg-red-700 text-white',
            borderClass: 'border-red-200 dark:border-red-800',
        },
        warning: {
            icon: AlertTriangle,
            iconClass: 'text-orange-500',
            iconBg: 'bg-orange-100 dark:bg-orange-900/30',
            confirmClass: 'bg-orange-600 hover:bg-orange-700 text-white',
            borderClass: 'border-orange-200 dark:border-orange-800',
        },
        info: {
            icon: Info,
            iconClass: 'text-blue-500',
            iconBg: 'bg-blue-100 dark:bg-blue-900/30',
            confirmClass: 'bg-blue-600 hover:bg-blue-700 text-white',
            borderClass: 'border-blue-200 dark:border-blue-800',
        },
        success: {
            icon: CheckCircle,
            iconClass: 'text-green-500',
            iconBg: 'bg-green-100 dark:bg-green-900/30',
            confirmClass: 'bg-green-600 hover:bg-green-700 text-white',
            borderClass: 'border-green-200 dark:border-green-800',
        },
    };
    return configs[options.value.variant || 'danger'];
});

const show = (opts: ConfirmDialogOptions): Promise<boolean> => {
    options.value = {
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        variant: 'danger',
        ...opts,
    };
    isOpen.value = true;

    return new Promise((resolve) => {
        resolvePromise = resolve;
    });
};

const handleConfirm = () => {
    isOpen.value = false;
    resolvePromise?.(true);
    resolvePromise = null;
};

const handleCancel = () => {
    isOpen.value = false;
    resolvePromise?.(false);
    resolvePromise = null;
};

// Expose the show method
defineExpose({ show });
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                @click.self="handleCancel"
            >
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div
                        v-if="isOpen"
                        class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden"
                        @click.stop
                    >
                        <!-- Close Button -->
                        <button
                            @click="handleCancel"
                            class="absolute top-4 right-4 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <X :size="18" />
                        </button>

                        <!-- Content -->
                        <div class="p-6">
                            <!-- Icon -->
                            <div :class="['w-12 h-12 rounded-full flex items-center justify-center mb-4', variantConfig.iconBg]">
                                <component :is="variantConfig.icon" :class="['w-6 h-6', variantConfig.iconClass]" />
                            </div>

                            <!-- Title -->
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                {{ options.title }}
                            </h3>

                            <!-- Message -->
                            <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                                {{ options.message }}
                            </p>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button
                                    @click="handleCancel"
                                    class="flex-1 px-4 py-2.5 rounded-lg font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700"
                                >
                                    {{ options.cancelText }}
                                </button>
                                <button
                                    @click="handleConfirm"
                                    :class="['flex-1 px-4 py-2.5 rounded-lg font-medium transition-colors shadow-lg', variantConfig.confirmClass]"
                                >
                                    {{ options.confirmText }}
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
