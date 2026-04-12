<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarRange, Users } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import workshops from '@/routes/workshops';

export type WorkshopSummary = {
    id: number;
    title: string;
    description: string | null;
    starts_at: string;
    ends_at: string;
    capacity: number;
};

defineProps<{
    upcomingWorkshops: WorkshopSummary[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Workshops',
                href: workshops.index.url(),
            },
        ],
    },
});

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
    <Head title="Workshops" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
        <Heading
            title="Upcoming workshops"
            description="Sessions that have not started yet, ordered by start time."
        />

        <ul
            v-if="upcomingWorkshops.length"
            class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
        >
            <li v-for="w in upcomingWorkshops" :key="w.id">
                <Card class="h-full border-sidebar-border/70 shadow-sm dark:border-sidebar-border">
                    <CardHeader class="space-y-1">
                        <CardTitle class="text-lg leading-snug">
                            {{ w.title }}
                        </CardTitle>
                        <CardDescription v-if="w.description" class="line-clamp-3">
                            {{ w.description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <CalendarRange class="size-4 shrink-0" aria-hidden="true" />
                            <span>{{ formatRange(w.starts_at, w.ends_at) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Users class="size-4 shrink-0" aria-hidden="true" />
                            <span>Capacity {{ w.capacity }}</span>
                        </div>
                    </CardContent>
                </Card>
            </li>
        </ul>

        <p v-else class="text-sm text-muted-foreground">
            No upcoming workshops yet.
        </p>
    </div>
</template>
