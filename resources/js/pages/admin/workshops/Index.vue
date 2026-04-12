<script setup lang="ts">
import { Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import Heading from "@/components/Heading.vue";
import type { WorkshopTableColumn } from "@/components/tables/types";
import WorkshopsIndexTable from "@/components/tables/WorkshopsIndexTable.vue";
import { Button } from "@/components/ui/button";
import type { WorkshopListItem } from "@/types/models";
import adminWorkshops from "@/routes/admin/workshops";

const props = defineProps<{
  workshopList: WorkshopListItem[];
  filters: Record<string, unknown>;
  showWorkshopTable: boolean;
  workshopTableColumns: WorkshopTableColumn[];
  employeeFilterFields: WorkshopTableColumn[];
}>();

const page = usePage();
const canManageWorkshops = computed(
  () => page.props.auth.workshop_permissions.manage === true
);

const tableRows = computed(() =>
  props.workshopList.map((w) => ({ ...w } as Record<string, unknown>))
);

const indexUrl = (query: Record<string, string>) => adminWorkshops.index.url({ query });

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: "Workshops",
        href: adminWorkshops.index.url(),
      },
    ],
  },
});
</script>

<template>
  <Head title="Workshops" />

  <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <Heading
        title="Workshops"
        description="Filter and review every session. Upcoming rows are listed before past ones."
      />
      <Button v-if="canManageWorkshops" variant="outline" disabled>
        Create workshop
      </Button>
    </div>

    <WorkshopsIndexTable
      :columns="workshopTableColumns"
      :rows="tableRows"
      :filters="filters"
      :index-url="indexUrl"
    />
  </div>
</template>
