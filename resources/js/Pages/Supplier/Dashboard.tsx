import PageHeader from '@/Components/PageHeader';
import StatCard from '@/Components/StatCard';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate } from '@/lib/format-date';
import type { WeeklyMenu } from '@/types';
import { Link } from '@inertiajs/react';

type Props = {
  activeWeek: WeeklyMenu | null;
  stats: {
    configuredDays: number;
    todaySelections: number;
    visibleOptions: number;
  };
};

export default function Dashboard({ activeWeek, stats }: Props) {
  return (
    <AppLayout>
      <PageHeader
        title="Panel del proveedor"
        description="Publica menús, revisa selecciones y ajusta la semana activa."
        action={{ label: 'Crear semana', href: route('supplier.weekly-menus.create') }}
      />

      <div className="grid gap-4 md:grid-cols-3">
        <StatCard label="Días configurados" value={stats.configuredDays} />
        <StatCard label="Selecciones hoy" value={stats.todaySelections} />
        <StatCard label="Opciones visibles" value={stats.visibleOptions} />
      </div>

      <div className="panel-card mt-6">
        <div className="card-body">
          <h2 className="card-title">Semana activa</h2>
          {activeWeek ? (
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <p className="text-xl font-semibold">{activeWeek.title}</p>
                <p className="text-sm text-base-content/60">
                  {formatDisplayDate(activeWeek.week_start_date)} al {formatDisplayDate(activeWeek.week_end_date)}
                </p>
              </div>
              <Link
                className="btn btn-primary"
                href={route('supplier.weekly-menus.index', { view: 'week', date: activeWeek.week_start_date })}
              >
                Abrir calendario
              </Link>
            </div>
          ) : (
            <p className="text-sm text-base-content/70">No hay una semana activa todavía.</p>
          )}
        </div>
      </div>
    </AppLayout>
  );
}
