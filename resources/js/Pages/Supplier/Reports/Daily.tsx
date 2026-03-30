import DataTable from '@/Components/DataTable';
import EmptyState from '@/Components/EmptyState';
import InputField from '@/Components/forms/InputField';
import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate } from '@/lib/format-date';
import { Link, router } from '@inertiajs/react';

type Props = {
  selectedDate: string;
  availableDates: string[];
  report: {
    date: string;
    weekTitle: string;
    totalSelections: number;
    options: { id: number; title: string; description?: string; totalSelections: number }[];
  } | null;
};

export default function Daily({ selectedDate, availableDates, report }: Props) {
  return (
    <AppLayout>
      <PageHeader
        title={report ? `Reporte diario ${formatDisplayDate(report.date)}` : 'Reporte diario'}
        description={report ? report.weekTitle : 'Selecciona una fecha. Por defecto se muestra hoy aunque aún no tenga datos.'}
        extra={
          <>
            <div className="min-w-56">
              <InputField
                label="Fecha"
                name="date"
                type="date"
                value={selectedDate}
                onChange={(value) =>
                  router.get(route('supplier.reports.daily'), { date: value }, { preserveState: true, preserveScroll: true })
                }
              />
            </div>
            <Link
              href={route('supplier.reports.daily.export', { date: selectedDate })}
              className={`btn btn-outline rounded-full px-5 ${report ? '' : 'btn-disabled pointer-events-none'}`}
            >
              Exportar Excel
            </Link>
          </>
        }
      />
      {availableDates.length > 0 ? (
        <div className="mb-6 flex flex-wrap gap-2">
          {availableDates.slice(0, 8).map((date) => (
            <Link
              key={date}
              href={route('supplier.reports.daily', { date })}
              className={`btn btn-sm rounded-full ${date === selectedDate ? 'btn-primary' : 'btn-ghost border border-base-300'}`}
            >
              {formatDisplayDate(date)}
            </Link>
          ))}
        </div>
      ) : null}
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
