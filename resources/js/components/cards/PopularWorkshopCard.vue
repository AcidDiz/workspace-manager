<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import adminWorkshops from "@/routes/admin/workshops";
import type { AdminWorkshopPopular } from "@/types/dashboard";

defineProps<{
  workshop: AdminWorkshopPopular | null;
}>();
</script>

<template>
  <div
    class="rounded-xl border border-sidebar-border/70 bg-card p-4 shadow-sm dark:border-sidebar-border"
  >
    <p class="text-sm text-muted-foreground">Most popular workshop</p>
    <template v-if="workshop">
      <p class="mt-2 font-medium leading-snug">{{ workshop.title }}</p>
      <p class="mt-1 text-sm text-muted-foreground">
        {{ workshop.confirmed_registrations_count }} confirmed
        {{ workshop.confirmed_registrations_count === 1 ? "seat" : "seats" }}
      </p>
      <Button class="mt-3" variant="outline" size="sm" as-child>
        <Link :href="adminWorkshops.show.url(workshop.id)">Open workshop</Link>
      </Button>
    </template>
    <p v-else class="mt-2 text-sm text-muted-foreground">
      No confirmed registrations yet — popularity appears once someone confirms a seat.
    </p>
  </div>
</template>
