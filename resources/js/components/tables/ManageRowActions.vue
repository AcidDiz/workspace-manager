<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDeleteDialog from '@/components/dialogs/ConfirmDeleteDialog.vue';
import { Button } from '@/components/ui/button';

withDefaults(
    defineProps<{
        editHref: string;
        /** Wayfinder `*.form(...)` for the destroy/delete route. */
        deleteForm: Record<string, unknown>;
        dialogTitle: string;
        dialogDescription: string;
        confirmLabel?: string;
        /** Pest Browser: `@${deleteTriggerDataTest}` */
        deleteTriggerDataTest?: string;
        confirmDeleteDataTest?: string;
    }>(),
    {
        confirmLabel: 'Delete',
    },
);

const deleteOpen = ref(false);
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Button variant="outline" size="sm" as-child>
            <Link :href="editHref">Edit</Link>
        </Button>
        <Button
            variant="destructive"
            size="sm"
            type="button"
            :data-test="deleteTriggerDataTest"
            @click="deleteOpen = true"
        >
            Delete
        </Button>
        <ConfirmDeleteDialog
            v-model:open="deleteOpen"
            :form-attributes="deleteForm"
            :title="dialogTitle"
            :description="dialogDescription"
            :confirm-label="confirmLabel"
            :confirm-data-test="confirmDeleteDataTest"
        />
    </div>
</template>
