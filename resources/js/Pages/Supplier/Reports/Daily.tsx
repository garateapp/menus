import DataTable from '@/Components/DataTable';
import EmptyState from '@/Components/EmptyState';
import InputField from '@/Components/forms/InputField';
import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate, formatDisplayDateTime } from '@/lib/format-date';
import { Link, router } from '@inertiajs/react';

type Props = {
  selectedDate: string;
  availableDates: string[];
  report: {
    date: string;
    weekTitle: string;
    totalSelections: number;
    options: { id: number; title: string; description?: string; totalSelections: number }[];
    selections: {
      id: number;
      userName?: string | null;
      userEmail?: string | null;
      username?: string | null;
      optionTitle?: string | null;
      selectedAt?: string | null;
    }[];
    noResponses: {
      id: number;
      userName?: string | null;
      userEmail?: string | null;
      username?: string | null;
    }[];
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
            {report ? (
              <a
                href={route('supplier.reports.daily.export', { date: selectedDate })}
                className="btn btn-outline rounded-full px-5"
              >
                Exportar Excel
              </a>
            ) : (
              <span className="btn btn-outline btn-disabled pointer-events-none rounded-full px-5">
                Exportar Excel
              </span>
            )}
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
          <div className="mt-8 space-y-4">
            <div className="panel-card">
              <div className="card-body">
                <h2 className="card-title">Detalle de entrega por usuario</h2>
                <p className="text-sm text-base-content/60">
                  Este bloque permite validar qué pidió cada trabajador para la entrega individual.
                </p>
              </div>
            </div>
            <DataTable
              rows={report.selections}
              columns={[
                { key: 'userName', header: 'Usuario', render: (row) => row.userName || 'Sin nombre' },
                { key: 'userEmail', header: 'Correo', render: (row) => row.userEmail || 'Sin correo' },
                { key: 'username', header: 'Username', render: (row) => row.username || '-' },
                { key: 'optionTitle', header: 'Menú elegido', render: (row) => row.optionTitle || 'Sin alternativa' },
                { key: 'selectedAt', header: 'Registrado', render: (row) => formatDisplayDateTime(row.selectedAt) },
              ]}
            />
          </div>
          <div className="mt-8 space-y-4">
            <div className="panel-card">
              <div className="card-body">
                <h2 className="card-title">Trabajadores sin respuesta</h2>
                <p className="text-sm text-base-content/60">
                  Este bloque lista a los trabajadores activos que todavía no han realizado ninguna selección para el día.
                </p>
              </div>
            </div>
            <DataTable
              rows={report.noResponses}
              columns={[
                { key: 'userName', header: 'Usuario', render: (row) => row.userName || 'Sin nombre' },
                { key: 'userEmail', header: 'Correo', render: (row) => row.userEmail || 'Sin correo' },
                { key: 'username', header: 'Username', render: (row) => row.username || '-' },
                { key: 'status', header: 'Estado', render: () => 'Sin respuesta' },
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
