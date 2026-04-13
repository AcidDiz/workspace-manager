<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{
    /** Display label (e.g. Upcoming / Closed). */
    label: string;
    /** Tailwind classes from `WorkshopStatusEnum::badgeClassName()` on the list payload. */
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
