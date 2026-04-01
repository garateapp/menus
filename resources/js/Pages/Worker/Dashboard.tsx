import PageHeader from '@/Components/PageHeader';
import StatCard from '@/Components/StatCard';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate } from '@/lib/format-date';
import type { DailyMenu, MenuSelection } from '@/types';
import { Link } from '@inertiajs/react';

type Props = {
  todayMenu: DailyMenu | null;
  upcomingMenus: DailyMenu[];
  recentSelections: MenuSelection[];
};

export default function WorkerDashboard({ todayMenu, upcomingMenus, recentSelections }: Props) {
  const todaySelection = todayMenu?.selections?.[0];

  return (
    <AppLayout>
      <PageHeader title="Panel del trabajador" description="Revisa el menú disponible y confirma tu almuerzo por día." />

      <div className="grid gap-4 md:grid-cols-3">
        <StatCard label="Menú de hoy" value={todayMenu ? 'Disponible' : 'Sin publicar'} />
        <StatCard label="Próximos días" value={upcomingMenus.length} />
        <StatCard label="Selección de hoy" value={todaySelection ? 'Registrada' : 'Pendiente'} />
      </div>

      <div className="mt-6 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div className="panel-card">
          <div className="card-body">
            <h2 className="card-title">Menús próximos</h2>
            <div className="space-y-3">
              {upcomingMenus.map((menu) => {
                const options = (menu.menu_options ?? menu.menuOptions ?? []).filter((option) => !option.is_opt_out);
                const menuDate = menu.menu_date;

                return (
                  <Link
                    key={menu.id}
                    href={route('worker.menus.index', { view: 'week', date: menuDate, selected_date: menuDate })}
                    className="flex items-center justify-between rounded-2xl bg-base-200 px-4 py-3"
                  >
                    <div>
                      <p className="font-medium">{formatDisplayDate(menuDate)}</p>
                      <p className="text-sm text-base-content/60">{options.length} alternativas</p>
                    </div>
                    <span className="btn btn-sm btn-outline">Ver</span>
                  </Link>
                );
              })}
            </div>
          </div>
        </div>

        <div className="panel-card">
          <div className="card-body">
            <h2 className="card-title">Historial reciente</h2>
            <div className="space-y-3">
              {recentSelections.map((selection) => (
                <div key={selection.id} className="rounded-2xl bg-base-200 px-4 py-3">
                  <p className="font-medium">{selection.menu_option?.title ?? selection.menuOption?.title}</p>
                  <p className="text-sm text-base-content/60">{formatDisplayDate(selection.daily_menu?.menu_date ?? selection.dailyMenu?.menu_date)}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
