export type TableColumnOption = {
    value: string;
    label: string;
};

/** Query-string filter field for {@link FiltersBar} (card index or derived from table columns). */
export type FilterBarField = {
    param: string;
    label: string;
    placeholder?: string | null;
    input_type?: 'text' | 'number' | 'date' | 'select' | 'checkbox' | null;
    options?: TableColumnOption[];
};

export type TableColumn = {
    field_name: string;
    label: string;
    placeholder?: string | null;
    cast_type: string;
    input_type?: 'text' | 'number' | 'date' | 'select' | 'checkbox' | null;
    filterable: boolean;
    /** Query string key when different from field_name (e.g. category_id for category.name). */
    filter_param?: string;
    options?: TableColumnOption[];
    sortable: boolean;
    default_sort: 'asc' | 'desc' | null;
};

/** @deprecated Use TableColumn */
export type WorkshopTableColumnOption = TableColumnOption;
/** @deprecated Use TableColumn */
export type WorkshopTableColumn = TableColumn;
