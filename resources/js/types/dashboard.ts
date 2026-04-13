export type AdminWorkshopPopular = {
  id: number;
  title: string;
  confirmed_registrations_count: number;
};

export type AdminWorkshopStatistics = {
  workshops: {
    total: number;
    upcoming: number;
    closed: number;
  };
  registrations: {
    confirmed: number;
    waiting_list: number;
    total: number;
  };
  popular_workshop: AdminWorkshopPopular | null;
  generated_at: string;
};
