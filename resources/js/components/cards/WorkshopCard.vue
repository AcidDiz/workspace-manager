<script setup lang="ts">
import { CalendarRange, Users } from 'lucide-vue-next';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { WorkshopListItem } from '@/types/models';

const props = defineProps<{
    workshop: WorkshopListItem;
}>();

function formatRange(startsAt: string, endsAt: string): string {
    const start = new Date(startsAt);
    const end = new Date(endsAt);

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).formatRange(start, end);
}
</script>

<template>
    <Card
        class="h-full border-sidebar-border/70 shadow-sm dark:border-sidebar-border"
    >
        <CardHeader class="space-y-1">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <CardTitle class="text-lg leading-snug">
                    {{ props.workshop.title }}
                </CardTitle>
                <span
                    v-if="props.workshop.category?.name"
                    class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                >
                    {{ props.workshop.category.name }}
                </span>
            </div>
            <CardDescription
                v-if="props.workshop.description"
                class="line-clamp-3"
            >
                {{ props.workshop.description }}
            </CardDescription>
        </CardHeader>
        <CardContent class="space-y-3 text-sm text-muted-foreground">
            <div class="flex items-center gap-2">
                <CalendarRange class="size-4 shrink-0" aria-hidden="true" />
                <span>{{ formatRange(props.workshop.starts_at, props.workshop.ends_at) }}</span>
            </div>
            <div class="flex items-center gap-2">
                <Users class="size-4 shrink-0" aria-hidden="true" />
                <span>Capacity {{ props.workshop.capacity }}</span>
            </div>
            <p class="text-xs font-medium text-foreground/80">
                {{
                    props.workshop.timing_status === 'upcoming'
                        ? 'Upcoming'
                        : 'Closed'
                }}
            </p>
        </CardContent>
    </Card>
</template>
