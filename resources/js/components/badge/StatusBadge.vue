<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{
    label: string;
    /** Tailwind utility classes for background, text, and border (from server or design tokens). */
    badgeClass: string;
}>();
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium',
                props.badgeClass,
            )
        "
    >
        {{ props.label }}
    </span>
</template>
