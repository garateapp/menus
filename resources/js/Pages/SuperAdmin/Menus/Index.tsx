import DataTable from '@/Components/DataTable';
import PageHeader from '@/Components/PageHeader';
import Pagination from '@/Components/Pagination';
import StatusBadge from '@/Components/StatusBadge';
import AppLayout from '@/Layouts/AppLayout';
import type { PaginatedResponse, WeeklyMenu } from '@/types';

export default function Index({ weeklyMenus }: { weeklyMenus: PaginatedResponse<WeeklyMenu> }) {
  return (
    <AppLayout>
      <PageHeader title="Todos los menús" description="Vista global de semanas y proveedor asociado." />
      <DataTable
        rows={weeklyMenus.data}
        columns={[
          { key: 'title', header: 'Semana', render: (row) => row.title },
          { key: 'supplier', header: 'Proveedor', render: (row) => row.supplier?.name ?? 'Proveedor' },
          { key: 'status', header: 'Estado', render: (row) => <StatusBadge status={row.status} /> },
          { key: 'days', header: 'Días', render: (row) => row.daily_menus?.length ?? 0 },
        ]}
      />
      <Pagination links={weeklyMenus.links} />
    </AppLayout>
  );
}
