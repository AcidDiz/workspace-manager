<script setup lang="ts">
import AdminSidebarLayout from '@/layouts/admin/AdminSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AdminSidebarLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AdminSidebarLayout>
</template>
