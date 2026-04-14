import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useEchoPrivateEvent } from '@/composables/useEchoPrivateEvent';
import type {
    WorkshopListItem,
    WorkshopRealtimeRegistrationState,
} from '@/types/models';

type UseRealtimeWorkshopRegistrationStateOptions = {
    applyState: (payload: WorkshopRealtimeRegistrationState) => void;
};

export function useRealtimeWorkshopRegistrationState({
    applyState,
}: UseRealtimeWorkshopRegistrationStateOptions): void {
    const page = usePage();
    const channel = computed(() => {
        const userId = page.props.auth.user?.id;

        return typeof userId === 'number' ? `App.Models.User.${userId}` : '';
    });

    useEchoPrivateEvent<{
        'workshops.registration-state.updated': WorkshopRealtimeRegistrationState;
    }>({
        channel,
        enabled: computed(() => channel.value !== ''),
        listeners: {
            'workshops.registration-state.updated': applyState,
        },
    });
}

export function applyRealtimeWorkshopRegistrationState(
    workshops: WorkshopListItem[],
    payload: WorkshopRealtimeRegistrationState,
): WorkshopListItem[] {
    return workshops.map((workshop) =>
        workshop.id === payload.workshop_id
            ? {
                  ...workshop,
                  my_registration_status: payload.registration_status,
                  my_waiting_list_position: payload.waiting_list_position,
              }
            : workshop,
    );
}
