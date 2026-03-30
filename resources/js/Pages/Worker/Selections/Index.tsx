import DataTable from '@/Components/DataTable';
import PageHeader from '@/Components/PageHeader';
import Pagination from '@/Components/Pagination';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate } from '@/lib/format-date';
import type { MenuSelection, PaginatedResponse } from '@/types';

export default function Index({ selections }: { selections: PaginatedResponse<MenuSelection> }) {
  return (
    <AppLayout>
      <PageHeader title="Mis selecciones" description="Historial reciente de tus elecciones por fecha." />
      <DataTable
        rows={selections.data}
        columns={[
          { key: 'date', header: 'Fecha', render: (row) => formatDisplayDate(row.daily_menu?.menu_date ?? row.dailyMenu?.menu_date) },
          { key: 'option', header: 'Opción', render: (row) => row.menu_option?.title ?? row.menuOption?.title },
          { key: 'selected_at', header: 'Registrada', render: (row) => formatDisplayDate(row.selected_at) },
        ]}
      />
      <Pagination links={selections.links} />
    </AppLayout>
  );
}
