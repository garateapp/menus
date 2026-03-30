import DataTable from '@/Components/DataTable';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate } from '@/lib/format-date';

type Props = {
  report: {
    date: string;
    weekTitle: string;
    totalSelections: number;
    options: { id: number; title: string; description?: string; totalSelections: number }[];
  } | null;
};

export default function Daily({ report }: Props) {
  return (
    <AppLayout>
      <PageHeader
        title={report ? `Reporte diario ${formatDisplayDate(report.date)}` : 'Reporte diario'}
        description={report ? report.weekTitle : 'Último día disponible del proveedor. Si no hay datos, verás este estado vacío.'}
      />
      {report ? (
        <>
          <div className="stat rounded-3xl border border-base-300 bg-base-100 shadow-sm">
            <div className="stat-title">Total a confeccionar</div>
            <div className="stat-value text-primary">{report.totalSelections}</div>
          </div>
          <div className="mt-6">
            <DataTable
              rows={report.options}
              columns={[
                { key: 'title', header: 'Alternativa', render: (row) => row.title },
                { key: 'description', header: 'Descripción', render: (row) => row.description || 'Sin descripción' },
                { key: 'count', header: 'Selecciones', render: (row) => row.totalSelections },
              ]}
            />
          </div>
        </>
      ) : (
        <EmptyState
          title="Sin datos para reporte diario"
          description="Aún no hay días con menú registrados para este proveedor."
        />
      )}
    </AppLayout>
  );
}
