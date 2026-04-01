import CalendarDrawer from '@/Components/calendar/CalendarDrawer';
import CalendarToolbar from '@/Components/calendar/CalendarToolbar';
import MenuOptOutSwitch from '@/Components/MenuOptOutSwitch';
import MenuOptionCard from '@/Components/MenuOptionCard';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate } from '@/lib/format-date';
import type { CalendarCell, CalendarRange, DailyMenu, MenuSelection } from '@/types';
import { Link } from '@inertiajs/react';

type Props = {
  calendar: CalendarRange;
  selectedDate?: string | null;
  selectedMenu?: DailyMenu | null;
};

function buildWorkerCalendarHref(calendar: CalendarRange, selectedDate?: string | null) {
  return route('worker.menus.index', {
    view: calendar.view,
    date: calendar.focus_date,
    ...(selectedDate ? { selected_date: selectedDate } : {}),
  });
}

function WorkerCalendarCell({ calendar, cell, selectedDate }: { calendar: CalendarRange; cell: CalendarCell; selectedDate?: string | null }) {
  const isSelected = selectedDate === cell.date;

  return (
    <Link
      href={buildWorkerCalendarHref(calendar, cell.date)}
      className={`flex min-h-[150px] flex-col rounded-[24px] border p-4 text-left transition-all duration-200 ${
        isSelected
          ? 'border-primary bg-primary/6 shadow-[0_18px_36px_-26px_rgba(59,130,246,0.6)]'
          : 'border-base-300 bg-base-100 hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-[0_18px_34px_-24px_rgba(15,23,42,0.18)]'
      } ${cell.is_current_month ? '' : 'opacity-55'}`}
    >
      <div className="flex items-start justify-between gap-3">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.18em] text-base-content/42">{cell.day_name}</p>
          <p className={`mt-2 font-display text-2xl font-semibold ${cell.is_today ? 'text-primary' : 'text-base-content'}`}>
            {cell.day_number}
          </p>
        </div>
        {cell.status ? <StatusBadge status={cell.status} /> : <span className="badge badge-ghost">Sin menú</span>}
      </div>

      <div className="mt-auto space-y-2 pt-5 text-sm text-base-content/62">
        <p>{cell.options_count ?? 0} alternativas</p>
        {cell.has_selection ? (
          <p className="rounded-2xl bg-success/10 px-3 py-2 text-success">
            Seleccionado: {cell.selection_title}
          </p>
        ) : (
          <p>{cell.has_day ? 'Disponible para revisar' : 'No hay menú publicado'}</p>
        )}
      </div>
    </Link>
  );
}

function WorkerDayAgenda({ calendar, selectedDate }: { calendar: CalendarRange; selectedDate?: string | null }) {
  return (
    <div className={`grid gap-4 ${calendar.view === 'month' ? 'md:grid-cols-2 xl:grid-cols-7' : calendar.view === 'week' ? 'md:grid-cols-2 xl:grid-cols-7' : 'grid-cols-1'}`}>
      {calendar.cells.map((cell) => (
        <WorkerCalendarCell key={cell.date} calendar={calendar} cell={cell} selectedDate={selectedDate} />
      ))}
    </div>
  );
}

export default function Index({ calendar, selectedDate, selectedMenu }: Props) {
  const closeHref = buildWorkerCalendarHref(calendar);
  const currentSelection = (selectedMenu?.selections?.[0] ?? null) as MenuSelection | null;
  const options = selectedMenu?.menu_options ?? selectedMenu?.menuOptions ?? [];
  const optOutOption = options.find((option) => option.is_opt_out);
  const regularOptions = options.filter((option) => !option.is_opt_out);

  return (
    <AppLayout>
      <PageHeader
        title="Calendario de menús"
        description="Navega por mes, semana o día para revisar el menú publicado y seleccionar tu alternativa desde el calendario."
      />

      <CalendarToolbar routeName="worker.menus.index" calendar={calendar} />

      <WorkerDayAgenda calendar={calendar} selectedDate={selectedDate} />

      <CalendarDrawer
        open={Boolean(selectedDate)}
        closeHref={closeHref}
        title={selectedDate ? `Menú del ${formatDisplayDate(selectedDate)}` : 'Detalle del día'}
        description="Selecciona o modifica tu opción directamente desde el calendario."
      >
        {!selectedMenu ? (
          <div className="panel-card">
            <div className="card-body">
              <p className="text-base font-semibold">Sin menú disponible</p>
              <p className="text-sm leading-6 text-base-content/60">
                Para esta fecha no hay un menú publicado o todavía no se han cargado alternativas.
              </p>
            </div>
          </div>
        ) : (
          <div className="space-y-5">
            <div className="panel-card">
              <div className="card-body flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <p className="text-sm font-medium text-base-content/55">Estado del día</p>
                  <div className="mt-2 flex flex-wrap items-center gap-3">
                    <StatusBadge status={selectedMenu.status} />
                    <span className="text-sm text-base-content/62">
                      {regularOptions.length} alternativas disponibles
                    </span>
                  </div>
                </div>

                {currentSelection ? (
                  <div className="rounded-[20px] bg-success/10 px-4 py-3 text-sm text-success">
                    Tu selección actual: {currentSelection.menu_option?.title ?? currentSelection.menuOption?.title}
                  </div>
                ) : null}
              </div>
            </div>

            <div className="grid gap-5">
              {optOutOption ? (
                <MenuOptOutSwitch
                  dailyMenuId={selectedMenu.id}
                  option={optOutOption}
                  selected={currentSelection?.menu_option_id === optOutOption.id}
                  disabled={selectedMenu.status === 'closed'}
                />
              ) : null}

              {regularOptions.map((option) => (
                <MenuOptionCard
                  key={option.id}
                  dailyMenuId={selectedMenu.id}
                  option={option}
                  selected={currentSelection?.menu_option_id === option.id}
                  disabled={selectedMenu.status === 'closed'}
                />
              ))}
            </div>
          </div>
        )}
      </CalendarDrawer>
    </AppLayout>
  );
}
