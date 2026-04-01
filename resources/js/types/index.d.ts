export type RoleName = 'SuperAdmin' | 'Supplier' | 'Worker';
export type MenuStatus = 'draft' | 'published' | 'closed';
export type CalendarView = 'month' | 'week' | 'day';

export interface Role {
  id: number;
  name: RoleName;
}

export interface User {
  id: number;
  name: string;
  email: string;
  username?: string | null;
  google_id?: string | null;
  is_active?: boolean;
  email_verified_at?: string | null;
  roles?: Role[];
}

export interface WeeklyMenu {
  id: number;
  supplier_id: number;
  title: string;
  week_start_date: string;
  week_end_date: string;
  status: MenuStatus;
  notes?: string | null;
  daily_menus?: DailyMenu[];
  daily_menus_count?: number;
  supplier?: User;
}

export interface DailyMenu {
  id: number;
  weekly_menu_id: number;
  menu_date: string;
  status: MenuStatus;
  weekly_menu?: WeeklyMenu;
  weeklyMenu?: WeeklyMenu;
  menu_options?: MenuOption[];
  menuOptions?: MenuOption[];
  selections?: MenuSelection[];
}

export interface MenuOption {
  id: number;
  daily_menu_id: number;
  title: string;
  description?: string | null;
  image_path?: string | null;
  quota?: number | null;
  remaining_quota?: number | null;
  is_sold_out?: boolean;
  is_visible: boolean;
  is_opt_out?: boolean;
  sort_order: number;
  totalSelections?: number;
  selections_count?: number;
  selections?: MenuSelection[];
}

export interface MenuSelection {
  id: number;
  user_id: number;
  daily_menu_id: number;
  menu_option_id: number;
  selected_at: string;
  daily_menu?: DailyMenu;
  dailyMenu?: DailyMenu;
  menu_option?: MenuOption;
  menuOption?: MenuOption;
}

export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: PaginationLink[];
  total: number;
  from: number | null;
  to: number | null;
  current_page: number;
  last_page: number;
}

export interface CalendarCell {
  date: string;
  day_number: number;
  day_name: string;
  is_today: boolean;
  is_focus_date: boolean;
  is_current_month: boolean;
  status?: MenuStatus | null;
  options_count?: number;
  has_selection?: boolean;
  selection_title?: string | null;
  has_week?: boolean;
  has_day?: boolean;
  daily_menu_id?: number | null;
  weekly_menu_id?: number | null;
  title?: string | null;
  can_interact?: boolean;
}

export interface CalendarRange {
  view: CalendarView;
  focus_date: string;
  label: string;
  range_start: string;
  range_end: string;
  previous_date: string;
  next_date: string;
  days: CalendarCell[];
  cells: CalendarCell[];
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
  auth: {
    user: User | null;
  };
  flash: {
    success?: string | null;
    error?: string | null;
  };
};
