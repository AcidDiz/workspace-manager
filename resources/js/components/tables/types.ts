export type WorkshopTableColumnOption = {
    value: string;
    label: string;
};

export type WorkshopTableColumn = {
    field_name: string;
    label: string;
    placeholder?: string | null;
    cast_type: string;
    input_type?: 'text' | 'number' | 'date' | 'select' | 'checkbox' | null;
    filterable: boolean;
    /** Query string key when different from field_name (e.g. category_id for category.name). */
    filter_param?: string;
    options?: WorkshopTableColumnOption[];
    sortable: boolean;
    default_sort: 'asc' | 'desc' | null;
};
