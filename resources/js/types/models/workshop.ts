export type WorkshopCategoryRef = {
    id: number | null;
    name: string;
};

export type WorkshopCreatorRef = {
    id: number | null;
    name: string;
};

/** Row shape for workshop list (Inertia `workshopList` prop). */
export type WorkshopListItem = {
    id: number;
    title: string;
    description: string | null;
    starts_at: string;
    ends_at: string;
    capacity: number;
    category: WorkshopCategoryRef;
    creator: WorkshopCreatorRef;
    timing_status: 'upcoming' | 'closed';
};

/** @deprecated Use WorkshopListItem; kept for gradual renames. */
export type WorkshopSummary = WorkshopListItem;

export type WorkshopPermissions = {
    view: boolean;
    manage: boolean;
};
