<script setup lang="ts">
import { GraduationCap, LayoutGrid } from 'lucide-vue-next';
import ShellTopHeader from '@/components/layout/ShellTopHeader.vue';
import type { BreadcrumbItem, NavItem } from '@/types';
import admin from '@/routes/admin';
import adminWorkshops from '@/routes/admin/workshops';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const primaryDashboardHref = admin.dashboard.url();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: primaryDashboardHref,
        icon: LayoutGrid,
    },
    {
        title: 'Workshops',
        href: adminWorkshops.index.url(),
        icon: GraduationCap,
    },
];
</script>

<template>
    <ShellTopHeader
        :breadcrumbs="props.breadcrumbs"
        :primary-dashboard-href="primaryDashboardHref"
        :main-nav-items="mainNavItems"
    />
</template>
