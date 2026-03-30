import DataTable from '@/Components/DataTable';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate } from '@/lib/format-date';

type Props = {
  report: {
    weeklyMenuId: number;
    title: string;
    totalSelections: number;
    days: { id: number; menuDate: string; status: string; totalSelections: number }[];
  } | null;
};

export default function Weekly({ report }: Props) {
  return (
    <AppLayout>
      <PageHeader
        title="Reporte semanal"
        description={report ? report.title : 'Última semana disponible del proveedor. Si no hay datos, verás este estado vacío.'}
      />
      {report ? (
        <>
          <div className="stat rounded-3xl border border-base-300 bg-base-100 shadow-sm">
            <div className="stat-title">Total semanal</div>
            <div className="stat-value text-primary">{report.totalSelections}</div>
          </div>
          <div className="mt-6">
            <DataTable
              rows={report.days}
              columns={[
                { key: 'date', header: 'Fecha', render: (row) => formatDisplayDate(row.menuDate) },
                { key: 'status', header: 'Estado', render: (row) => row.status },
                { key: 'count', header: 'Selecciones', render: (row) => row.totalSelections },
              ]}
            />
          </div>
        </>
      ) : (
        <EmptyState
          title="Sin datos para reporte semanal"
          description="Aún no hay semanas con menús registrados para este proveedor."
        />
      )}
    </AppLayout>
  );
}
