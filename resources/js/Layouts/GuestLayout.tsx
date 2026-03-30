import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function GuestLayout({ children }: PropsWithChildren) {
  return (
    <div data-theme="emerald" className="relative min-h-screen overflow-hidden bg-base-200">
      <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.14),transparent_26%),radial-gradient(circle_at_bottom_right,rgba(5,150,105,0.12),transparent_24%)]" />

      <div className="relative flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:px-6">
        <Link href="/" className="mb-6 flex items-center gap-3 text-base-content">
          <ApplicationLogo className="h-12 w-12 fill-current text-primary" />
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/55">Packing Garate</p>
            <p className="font-display text-2xl font-semibold">Menú</p>
          </div>
        </Link>

        <div className="panel-card w-full max-w-lg bg-base-100/96 p-6 shadow-[0_24px_64px_-32px_rgba(6,95,70,0.35)] sm:p-8">
          {children}
        </div>
      </div>
    </div>
  );
}
