import type { CalendarRange, CalendarView } from '@/types';
import { Link } from '@inertiajs/react';

type Props = {
  routeName: string;
  calendar: CalendarRange;
};

const viewOptions: { label: string; value: CalendarView }[] = [
  { label: 'Mes', value: 'month' },
  { label: 'Semana', value: 'week' },
  { label: 'Día', value: 'day' },
];

function buildCalendarHref(routeName: string, view: CalendarView, date: string) {
  return route(routeName, {
    view,
    date,
  });
}

export default function CalendarToolbar({ routeName, calendar }: Props) {
  return (
    <div className="panel-card mb-6">
      <div className="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
        <div className="flex flex-wrap items-center gap-2">
          <Link href={buildCalendarHref(routeName, calendar.view, calendar.previous_date)} className="btn btn-ghost rounded-full border border-base-300 px-5">
            Anterior
          </Link>
          <Link href={buildCalendarHref(routeName, calendar.view, calendar.focus_date)} className="btn btn-primary rounded-full px-5">
            Hoy
          </Link>
          <Link href={buildCalendarHref(routeName, calendar.view, calendar.next_date)} className="btn btn-ghost rounded-full border border-base-300 px-5">
            Siguiente
          </Link>
        </div>

        <div className="order-first lg:order-none">
          <p className="section-kicker">Vista actual</p>
          <h2 className="mt-2 font-display text-2xl font-semibold text-base-content">{calendar.label}</h2>
        </div>

        <div className="join rounded-full border border-base-300 bg-base-100 p-1">
          {viewOptions.map((option) => {
            const isActive = option.value === calendar.view;

            return (
              <Link
                key={option.value}
                href={buildCalendarHref(routeName, option.value, calendar.focus_date)}
                className={`join-item btn rounded-full border-0 px-5 ${isActive ? 'btn-primary' : 'btn-ghost'}`}
              >
                {option.label}
              </Link>
            );
          })}
        </div>
      </div>
    </div>
  );
}
