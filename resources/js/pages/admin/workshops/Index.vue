<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import Heading from "@/components/Heading.vue";
import ManageRowActions from "@/components/tables/ManageRowActions.vue";
import Table from "@/components/tables/Table.vue";
import type { TableColumn } from "@/components/tables/types";
import { Button } from "@/components/ui/button";
import adminWorkshops from "@/routes/admin/workshops";
import type { WorkshopListItem } from "@/types/models";

const props = defineProps<{
  workshopList: WorkshopListItem[];
  filters: Record<string, unknown>;
  workshopTableColumns: TableColumn[];
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
      <Button v-if="canManageWorkshops" variant="outline" as-child>
        <Link :href="adminWorkshops.create.url()">Create workshop</Link>
      </Button>
    </div>

    <Table
      :columns="workshopTableColumns"
      :rows="tableRows"
      :filters="filters"
      :index-url="indexUrl"
      :show-manage-actions="canManageWorkshops"
      empty-message="No workshops match the current filters."
    >
      <template #row-actions="{ row }">
        <ManageRowActions
          :edit-href="adminWorkshops.edit.url(Number(row.id))"
          :delete-form="adminWorkshops.destroy.form(Number(row.id))"
          dialog-title="Delete workshop"
          :dialog-description="`This will remove “${String(row.title ?? '')}” and all related enrolment rows. This cannot be undone.`"
          confirm-label="Delete workshop"
          :delete-trigger-data-test="`delete-workshop-${Number(row.id)}`"
          confirm-delete-data-test="confirm-delete-workshop-button"
        />
      </template>
    </Table>
  </div>
</template>
