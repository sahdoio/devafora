<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 100 100"
        :class="className"
        v-bind="$attrs"
    >
        <!-- DevAfora Logo - D + A lettermark -->
        <defs>
            <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#2563eb;stop-opacity:1" />
            </linearGradient>
        </defs>
        <!-- Letter D -->
        <path
            fill="url(#logoGradient)"
            d="M 20 25 L 35 25 C 48 25 55 32 55 45 C 55 58 48 65 35 65 L 20 65 Z M 28 33 L 28 57 L 35 57 C 43 57 47 53 47 45 C 47 37 43 33 35 33 Z"
        />
        <!-- Letter A -->
        <path
            fill="url(#logoGradient)"
            d="M 62 65 L 70 45 L 78 65 L 85 65 L 73 25 L 67 25 L 55 65 Z M 70 38 L 75 52 L 65 52 Z"
        />
        <!-- Accent line -->
        <rect
            x="20"
            y="72"
            width="65"
            height="3"
            rx="1.5"
            fill="currentColor"
            opacity="0.3"
        />
    </svg>
</template>
