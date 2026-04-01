import DataTable from '@/Components/DataTable';
import EmptyState from '@/Components/EmptyState';
import InputField from '@/Components/forms/InputField';
import PageHeader from '@/Components/PageHeader';
import StatCard from '@/Components/StatCard';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate, formatDisplayDateTime } from '@/lib/format-date';
import { Link, router } from '@inertiajs/react';

type DailySelection = {
  id: number;
  userName?: string | null;
  userEmail?: string | null;
  username?: string | null;
  optionTitle?: string | null;
  selectedAt?: string | null;
};

type DailyNoResponse = {
  id: number;
  userName?: string | null;
  userEmail?: string | null;
  username?: string | null;
};

type DailyReport = {
  date: string;
  weekTitle: string;
  totalSelections: number;
  options: { id: number; title: string; description?: string; totalSelections: number }[];
  selections: DailySelection[];
  noResponses: DailyNoResponse[];
} | null;

type Props = {
  summary: Record<string, number>;
  selectedDate: string;
  availableDates: string[];
  dailyReport: DailyReport;
};

export default function Index({ summary, selectedDate, availableDates, dailyReport }: Props) {
  return (
    <AppLayout>
      <PageHeader
        title="Reportes globales"
        description="Resumen consolidado del sistema y control diario detallado por usuario."
        extra={
          <div className="min-w-56">
            <InputField
              label="Fecha"
              name="date"
              type="date"
              value={selectedDate}
              onChange={(value) =>
                router.get(route('superadmin.reports.index'), { date: value }, { preserveState: true, preserveScroll: true })
              }
            />
          </div>
        }
      />

      <div className="grid gap-4 md:grid-cols-3">
        <StatCard label="Selecciones totales" value={summary.totalSelections} />
        <StatCard label="Semanas registradas" value={summary.weeklyMenus} />
        <StatCard label="Selecciones hoy" value={summary.todaySelections} />
      </div>

      {availableDates.length > 0 ? (
        <div className="mt-6 flex flex-wrap gap-2">
          {availableDates.slice(0, 10).map((date) => (
            <Link
              key={date}
              href={route('superadmin.reports.index', { date })}
              className={`btn btn-sm rounded-full ${date === selectedDate ? 'btn-primary' : 'btn-ghost border border-base-300'}`}
            >
              {formatDisplayDate(date)}
            </Link>
          ))}
        </div>
      ) : null}

      {dailyReport ? (
        <div className="mt-8 space-y-8">
          <div className="panel-card">
            <div className="card-body">
              <h2 className="card-title">Control diario del {formatDisplayDate(dailyReport.date)}</h2>
              <p className="text-sm text-base-content/60">{dailyReport.weekTitle}</p>
            </div>
          </div>

          <div className="stat rounded-3xl border border-base-300 bg-base-100 shadow-sm">
            <div className="stat-title">Total a confeccionar</div>
            <div className="stat-value text-primary">{dailyReport.totalSelections}</div>
          </div>

          <DataTable
            rows={dailyReport.options}
            columns={[
              { key: 'title', header: 'Alternativa', render: (row) => row.title },
              { key: 'description', header: 'Descripción', render: (row) => row.description || 'Sin descripción' },
              { key: 'totalSelections', header: 'Selecciones', render: (row) => row.totalSelections },
            ]}
          />

          <div className="panel-card">
            <div className="card-body">
              <h2 className="card-title">Detalle por usuario</h2>
              <p className="text-sm text-base-content/60">
                Este control permite validar qué pidió cada trabajador y entregar el menú de forma individual.
              </p>
            </div>
          </div>

          <DataTable
            rows={dailyReport.selections}
            columns={[
              { key: 'userName', header: 'Usuario', render: (row) => row.userName || 'Sin nombre' },
              { key: 'userEmail', header: 'Correo', render: (row) => row.userEmail || 'Sin correo' },
              { key: 'username', header: 'Username', render: (row) => row.username || '-' },
              { key: 'optionTitle', header: 'Menú elegido', render: (row) => row.optionTitle || 'Sin alternativa' },
              { key: 'selectedAt', header: 'Registrado', render: (row) => formatDisplayDateTime(row.selectedAt) },
            ]}
          />

          <div className="panel-card">
            <div className="card-body">
              <h2 className="card-title">Trabajadores sin respuesta</h2>
              <p className="text-sm text-base-content/60">
                Nómina de trabajadores activos que aún no registran una elección para la fecha seleccionada.
              </p>
            </div>
          </div>

          <DataTable
            rows={dailyReport.noResponses}
            columns={[
              { key: 'userName', header: 'Usuario', render: (row) => row.userName || 'Sin nombre' },
              { key: 'userEmail', header: 'Correo', render: (row) => row.userEmail || 'Sin correo' },
              { key: 'username', header: 'Username', render: (row) => row.username || '-' },
              { key: 'status', header: 'Estado', render: () => 'Sin respuesta' },
            ]}
          />
        </div>
      ) : (
        <div className="mt-8">
          <EmptyState
            title="Sin datos para el control diario"
            description="Aún no existen selecciones registradas para la fecha seleccionada."
          />
        </div>
      )}
    </AppLayout>
  );
}
