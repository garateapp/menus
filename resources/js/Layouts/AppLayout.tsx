import FlashAlert from '@/Components/FlashAlert';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

import type { PageProps, RoleName } from '@/types';

type NavItem = {
  label: string;
  href: string;
  routeName: string;
  summary: string;
};

const navByRole: Record<RoleName, NavItem[]> = {
  Worker: [
    { label: 'Dashboard', href: route('worker.dashboard'), routeName: 'worker.dashboard', summary: 'Vista rápida de tu semana' },
    { label: 'Calendario', href: route('worker.menus.index'), routeName: 'worker.menus.*', summary: 'Revisa alternativas publicadas' },
    { label: 'Mis selecciones', href: route('worker.selections.index'), routeName: 'worker.selections.*', summary: 'Historial y cambios realizados' },
  ],
  Supplier: [
    { label: 'Dashboard', href: route('supplier.dashboard'), routeName: 'supplier.dashboard', summary: 'Monitorea la operación activa' },
    { label: 'Calendario', href: route('supplier.weekly-menus.index'), routeName: 'supplier.weekly-menus.*', summary: 'Gestiona semanas, días y opciones' },
    { label: 'Reporte diario', href: route('supplier.reports.daily'), routeName: 'supplier.reports.daily', summary: 'Totales por alternativa y día' },
    { label: 'Reporte semanal', href: route('supplier.reports.weekly'), routeName: 'supplier.reports.weekly', summary: 'Seguimiento acumulado de la semana' },
  ],
  SuperAdmin: [
    { label: 'Dashboard', href: route('superadmin.dashboard'), routeName: 'superadmin.dashboard', summary: 'Estado general del sistema' },
    { label: 'Usuarios', href: route('superadmin.users.index'), routeName: 'superadmin.users.*', summary: 'Roles, accesos y activaciones' },
    { label: 'Menus', href: route('superadmin.menus.index'), routeName: 'superadmin.menus.*', summary: 'Supervisión global de menús' },
    { label: 'Reportes', href: route('superadmin.reports.index'), routeName: 'superadmin.reports.*', summary: 'Indicadores y seguimiento global' },
  ],
};

const roleLabels: Record<RoleName, string> = {
  Worker: 'Trabajador',
  Supplier: 'Proveedor',
  SuperAdmin: 'SuperAdmin',
};

export default function AppLayout({ children }: PropsWithChildren) {
  const { auth, flash } = usePage<PageProps>().props;
  const user = auth.user;
  const role = (user?.roles?.[0]?.name ?? 'Worker') as RoleName;
  const navItems = navByRole[role] ?? [];

  return (
    <div data-theme="emerald" className="drawer min-h-screen bg-base-200 lg:drawer-open">
      <input id="app-drawer" type="checkbox" className="drawer-toggle" />

      <div className="drawer-content flex min-h-screen flex-col">
        <header className="sticky top-0 z-30 border-b border-base-300/70 bg-base-100/82 backdrop-blur-xl">
          <div className="page-shell flex min-h-[88px] items-center gap-4 py-4">
            <div className="flex flex-1 items-center gap-3">
              <label htmlFor="app-drawer" aria-label="Abrir navegación" className="btn btn-square btn-ghost border border-base-300 lg:hidden">
                <svg viewBox="0 0 24 24" className="h-5 w-5 fill-none stroke-current stroke-2" aria-hidden="true">
                  <path d="M4 7h16M4 12h16M4 17h16" strokeLinecap="round" />
                </svg>
              </label>

              <div className="hidden h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary lg:flex">
                <svg viewBox="0 0 24 24" className="h-5 w-5 fill-none stroke-current stroke-2" aria-hidden="true">
                  <path d="M4 12h16M7 7h10M7 17h10" strokeLinecap="round" />
                </svg>
              </div>

              <div>
                <p className="section-kicker">Packing Garate</p>
                <div className="mt-1 flex flex-wrap items-center gap-2">
                  <p className="font-display text-xl font-semibold text-base-content">Menú</p>
                  <span className="badge badge-primary badge-outline rounded-full border-base-300/80">
                    {roleLabels[role]}
                  </span>
                </div>
              </div>
            </div>

            <div className="hidden min-w-0 flex-1 items-center justify-center xl:flex">
              <div className="soft-surface flex w-full max-w-xl items-center gap-3 px-4 py-3">
                <div className="rounded-xl bg-primary/10 p-2 text-primary">
                  <svg viewBox="0 0 24 24" className="h-4 w-4 fill-none stroke-current stroke-2" aria-hidden="true">
                    <path d="M4 12h16M12 4v16" strokeLinecap="round" />
                  </svg>
                </div>
                <div className="min-w-0">
                  <p className="text-sm font-semibold text-base-content">Operación de almuerzos</p>
                  <p className="truncate text-xs text-base-content/55">Planificación semanal, selección diaria y seguimiento centralizado</p>
                </div>
              </div>
            </div>

            <div className="flex items-center gap-3">
              <div className="hidden text-right sm:block">
                <p className="text-sm font-semibold text-base-content">{user?.name}</p>
                <p className="text-xs text-base-content/55">{user?.email}</p>
              </div>
              <Link href={route('logout')} method="post" as="button" className="btn btn-primary btn-sm rounded-full px-5">
                Salir
              </Link>
            </div>
          </div>
        </header>

        <main className="page-shell flex-1 pb-10">
          <FlashAlert success={flash.success} error={flash.error} />
          {children}
        </main>
      </div>

      <div className="drawer-side z-40">
        <label htmlFor="app-drawer" className="drawer-overlay" />
        <aside className="flex min-h-full w-80 flex-col border-r border-base-300/80 bg-base-100/94 p-5 backdrop-blur-xl">
          <div className="glass-surface p-5">
            <div className="flex items-start justify-between gap-4">
              <div>
                <p className="section-kicker">Centro de control</p>
                <h2 className="mt-2 font-display text-2xl font-semibold text-base-content">Menú</h2>
                <p className="mt-2 text-sm leading-6 text-base-content/62">
                  Operación semanal de almuerzos con foco en velocidad, claridad y trazabilidad.
                </p>
              </div>
              <span className="badge badge-primary rounded-full border-none px-3 py-3 text-primary-content">
                {roleLabels[role]}
              </span>
            </div>
          </div>

          <div className="mt-6 flex-1 rounded-[28px] border border-base-300/70 bg-base-100/88 p-3 shadow-[0_20px_50px_-34px_rgba(15,23,42,0.35)]">
            <div className="px-3 pb-3 pt-2">
              <p className="section-kicker">Navegación</p>
            </div>

            <nav className="space-y-1.5">
              {navItems.map((item) => {
                const isActive = route().current(item.routeName);

                return (
                  <Link
                    key={item.routeName}
                    href={item.href}
                    className={isActive ? 'nav-link nav-link-active' : 'nav-link'}
                  >
                    <span className="min-w-0">
                      <span className="block truncate font-semibold">{item.label}</span>
                      <span className={`mt-1 block truncate text-xs ${isActive ? 'text-primary-content/80' : 'text-base-content/45'}`}>
                        {item.summary}
                      </span>
                    </span>
                    <svg viewBox="0 0 24 24" className={`h-4 w-4 fill-none stroke-current stroke-2 ${isActive ? 'opacity-90' : 'opacity-35'}`} aria-hidden="true">
                      <path d="M9 6l6 6-6 6" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  </Link>
                );
              })}

              <Link href={route('profile.edit')} className={route().current('profile.*') ? 'nav-link nav-link-active' : 'nav-link'}>
                <span className="min-w-0">
                  <span className="block truncate font-semibold">Mi perfil</span>
                  <span className={`mt-1 block truncate text-xs ${route().current('profile.*') ? 'text-primary-content/80' : 'text-base-content/45'}`}>
                    Ajusta datos personales y contraseña
                  </span>
                </span>
                <svg viewBox="0 0 24 24" className={`h-4 w-4 fill-none stroke-current stroke-2 ${route().current('profile.*') ? 'opacity-90' : 'opacity-35'}`} aria-hidden="true">
                  <path d="M9 6l6 6-6 6" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </Link>
            </nav>
          </div>

          <div className="soft-surface mt-6 p-4">
            <p className="text-sm font-semibold text-base-content">Sesión actual</p>
            <p className="mt-1 text-sm text-base-content/58">{user?.name}</p>
            <p className="text-xs text-base-content/48">{user?.email}</p>
          </div>
        </aside>
      </div>
    </div>
  );
}
