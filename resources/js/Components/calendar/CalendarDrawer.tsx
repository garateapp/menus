import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';

type Props = PropsWithChildren<{
  open: boolean;
  closeHref: string;
  title: string;
  description?: string;
}>;

export default function CalendarDrawer({ open, closeHref, title, description, children }: Props) {
  if (!open) {
    return null;
  }

  return (
    <div className="pointer-events-none fixed inset-0 z-50">
      <Link href={closeHref} className="pointer-events-auto absolute inset-0 bg-slate-950/28" aria-label="Cerrar panel" />

      <aside className="pointer-events-auto absolute inset-y-0 right-0 flex w-full max-w-2xl flex-col border-l border-base-300 bg-base-100 shadow-[0_24px_64px_-24px_rgba(15,23,42,0.4)]">
        <div className="flex items-start justify-between gap-4 border-b border-base-300 px-6 py-5">
          <div>
            <p className="section-kicker">Detalle del día</p>
            <h2 className="mt-2 font-display text-2xl font-semibold text-base-content">{title}</h2>
            {description ? <p className="mt-2 text-sm leading-6 text-base-content/58">{description}</p> : null}
          </div>

          <Link href={closeHref} className="btn btn-ghost btn-sm btn-circle" aria-label="Cerrar panel">
            ✕
          </Link>
        </div>

        <div className="flex-1 overflow-y-auto px-6 py-6">
          {children}
        </div>
      </aside>
    </div>
  );
}
