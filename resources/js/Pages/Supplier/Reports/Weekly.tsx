import DataTable from '@/Components/DataTable';
import EmptyState from '@/Components/EmptyState';
import SelectField from '@/Components/forms/SelectField';
import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate } from '@/lib/format-date';
import { Link, router } from '@inertiajs/react';

type Props = {
  availableWeeks: { id: number; title: string; weekStartDate: string; weekEndDate: string }[];
  selectedWeeklyMenuId: number | null;
  selectedDay?: string | null;
  report: {
    weeklyMenuId: number;
    title: string;
    totalSelections: number;
    days: { id: number; menuDate: string; status: string; totalSelections: number; options: { id: number; title: string; description?: string; totalSelections: number }[] }[];
    selectedDay?: {
      date: string;
      status: string;
      totalSelections: number;
      options: { id: number; title: string; description?: string; totalSelections: number }[];
    } | null;
  } | null;
};

export default function Weekly({ availableWeeks, selectedWeeklyMenuId, selectedDay, report }: Props) {
  const selectedWeekValue = selectedWeeklyMenuId ? String(selectedWeeklyMenuId) : report ? String(report.weeklyMenuId) : '';

  return (
    <AppLayout>
      <PageHeader
        title="Reporte semanal"
        description={report ? report.title : 'Selecciona una semana para revisar el consolidado y el detalle por día.'}
        extra={
          <>
            <div className="min-w-72">
              <SelectField
                label="Semana"
                name="weekly_menu_id"
                value={selectedWeekValue}
                onChange={(value) =>
                  router.get(route('supplier.reports.weekly'), { weekly_menu_id: value }, { preserveState: true, preserveScroll: true })
                }
                options={availableWeeks.map((week) => ({
                  label: `${week.title} (${formatDisplayDate(week.weekStartDate)} al ${formatDisplayDate(week.weekEndDate)})`,
                  value: String(week.id),
                }))}
              />
            </div>
            {report ? (
              <a
                href={route('supplier.reports.weekly.export', {
                  weekly_menu_id: selectedWeekValue,
                  ...(selectedDay ? { day: selectedDay } : {}),
                })}
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
      {report ? (
        <>
          <div className="stat rounded-3xl border border-base-300 bg-base-100 shadow-sm">
            <div className="stat-title">Total semanal</div>
            <div className="stat-value text-primary">{report.totalSelections}</div>
          </div>
          <div className="mt-6 flex flex-wrap gap-2">
            {report.days.map((day) => (
              <Link
                key={day.id}
                href={route('supplier.reports.weekly', {
                  weekly_menu_id: report.weeklyMenuId,
                  day: day.menuDate,
                })}
                className={`btn btn-sm rounded-full ${day.menuDate === (selectedDay ?? report.selectedDay?.date) ? 'btn-primary' : 'btn-ghost border border-base-300'}`}
              >
                {formatDisplayDate(day.menuDate)}
              </Link>
            ))}
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
          {report.selectedDay ? (
            <div className="mt-6 space-y-4">
              <div className="panel-card">
                <div className="card-body">
                  <h2 className="card-title">Detalle del día {formatDisplayDate(report.selectedDay.date)}</h2>
                  <p className="text-sm text-base-content/60">
                    Estado: {report.selectedDay.status} | Total a confeccionar: {report.selectedDay.totalSelections}
                  </p>
                </div>
              </div>
              <DataTable
                rows={report.selectedDay.options}
                columns={[
                  { key: 'title', header: 'Alternativa', render: (row) => row.title },
                  { key: 'description', header: 'Descripción', render: (row) => row.description || 'Sin descripción' },
                  { key: 'count', header: 'Selecciones', render: (row) => row.totalSelections },
                ]}
              />
            </div>
          ) : null}
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
