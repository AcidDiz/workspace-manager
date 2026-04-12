<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { WorkshopTableColumn } from '@/components/tables/types';
import {
    formatWorkshopCellValue,
    getNestedValue,
} from '@/components/tables/utils';
import WorkshopsFilterBar from '@/components/tables/WorkshopsFilterBar.vue';

const props = defineProps<{
    columns: WorkshopTableColumn[];
    rows: Record<string, unknown>[];
    filters: Record<string, unknown>;
    indexUrl: (query: Record<string, string>) => string;
}>();

function isSortedBy(column: WorkshopTableColumn): boolean {
    return props.filters.sort === column.field_name;
}

function currentDirection(): 'asc' | 'desc' | null {
    const raw = props.filters.direction;
    if (raw === 'asc' || raw === 'desc') {
        return raw;
    }
    return null;
}

function buildQuery(overrides: Record<string, string | null>): Record<string, string> {
    const query: Record<string, string> = {};

    for (const [key, value] of Object.entries(props.filters)) {
        if (value === null || value === undefined || value === '') {
            continue;
        }
        query[key] = String(value);
    }

    for (const [key, value] of Object.entries(overrides)) {
        if (value === null || value === '') {
            delete query[key];
            continue;
        }
        query[key] = value;
    }

    return query;
}

function toggleSort(column: WorkshopTableColumn): void {
    if (!column.sortable) {
        return;
    }

    const sortedByThis = isSortedBy(column);
    const dir = currentDirection();

    if (!sortedByThis) {
        router.get(
            props.indexUrl(buildQuery({
                sort: column.field_name,
                direction: String(column.default_sort ?? 'asc'),
            })),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
        return;
    }

    if (dir === 'asc') {
        router.get(
            props.indexUrl(buildQuery({ sort: column.field_name, direction: 'desc' })),
            {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
        return;
    }

    router.get(
        props.indexUrl(buildQuery({ sort: null, direction: null })),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <WorkshopsFilterBar
            :fields="columns"
            :filters="filters"
            :index-url="indexUrl"
        />

        <div class="overflow-x-auto rounded-md border border-sidebar-border/60 dark:border-sidebar-border">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="border-b border-sidebar-border/60 bg-muted/40 dark:border-sidebar-border">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.field_name"
                            class="whitespace-nowrap px-3 py-2 font-medium text-muted-foreground"
                        >
                            <button
                                v-if="col.sortable"
                                type="button"
                                class="inline-flex items-center gap-1 text-left hover:text-foreground"
                                @click="toggleSort(col)"
                            >
                                <span>{{ col.label }}</span>
                                <span v-if="isSortedBy(col)" class="text-xs">
                                    {{ currentDirection() === 'desc' ? '▼' : '▲' }}
                                </span>
                            </button>
                            <span v-else>{{ col.label }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="rows.length === 0">
                        <td
                            :colspan="columns.length || 1"
                            class="px-3 py-8 text-center text-muted-foreground"
                        >
                            No workshops match the current filters.
                        </td>
                    </tr>
                    <tr
                        v-for="row in rows"
                        v-else
                        :key="(row.id as number | string) ?? Math.random()"
                        class="border-b border-sidebar-border/40 odd:bg-muted/10 dark:border-sidebar-border/60"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.field_name"
                            class="whitespace-nowrap px-3 py-2 align-top"
                        >
                            {{
                                formatWorkshopCellValue(
                                    getNestedValue(row, col.field_name),
                                    col,
                                )
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
