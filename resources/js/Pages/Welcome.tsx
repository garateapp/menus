import { Head, Link } from '@inertiajs/react';
import type { PageProps } from '@/types';

const featureCards = [
  {
    title: 'Proveedor',
    caption: 'Gestión semanal',
    description: 'Carga semanas, publica días, sube alternativas con imágenes y monitorea demanda real.',
  },
  {
    title: 'Trabajador',
    caption: 'Selección simple',
    description: 'Revisa el menú disponible, ve cupos restantes y confirma una sola alternativa por día.',
  },
  {
    title: 'Administración',
    caption: 'Supervisión global',
    description: 'Controla usuarios, accesos, menús y reportes operacionales desde un solo panel.',
  },
];

export default function Welcome({
  auth,
  canLogin,
}: PageProps<{ canLogin: boolean; canRegister: boolean; laravelVersion: string; phpVersion: string }>) {
  const primaryAction = auth.user
    ? { label: 'Ir al panel', href: route('dashboard') }
    : { label: 'Iniciar sesión', href: route('login') };

  return (
    <>
      <Head title="Menús de almuerzo" />

      <div data-theme="emerald" className="relative min-h-screen overflow-hidden bg-base-200">
        <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(37,99,235,0.14),transparent_24%),radial-gradient(circle_at_bottom_right,rgba(14,165,233,0.11),transparent_22%)]" />

        <div className="relative mx-auto flex min-h-screen max-w-[1440px] flex-col px-4 py-6 sm:px-6 lg:px-8">
          <header className="soft-surface flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div className="flex items-center gap-4">
              <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                <svg viewBox="0 0 24 24" className="h-5 w-5 fill-none stroke-current stroke-2" aria-hidden="true">
                  <path d="M4 12h16M7 7h10M7 17h10" strokeLinecap="round" />
                </svg>
              </div>
              <div>
                <p className="section-kicker">Packing Garate</p>
                <p className="mt-1 font-display text-2xl font-semibold text-base-content">Menús de almuerzo</p>
              </div>
            </div>

            {canLogin ? (
              <Link href={primaryAction.href} className="btn btn-primary rounded-full px-6">
                {primaryAction.label}
              </Link>
            ) : null}
          </header>

          <main className="grid flex-1 items-center gap-10 py-10 lg:grid-cols-[1.08fr_0.92fr] lg:py-14">
            <section className="space-y-7">
              <div className="space-y-5">
                <span className="badge badge-primary badge-outline rounded-full px-4 py-3">
                  Planificación semanal y selección diaria
                </span>
                <h1 className="max-w-4xl font-display text-5xl font-semibold leading-[1.05] tracking-tight text-base-content sm:text-6xl xl:text-7xl">
                  Menús de almuerzo mejor coordinados, con menos fricción para toda la operación.
                </h1>
                <p className="max-w-2xl text-base leading-8 text-base-content/68 sm:text-lg">
                  Esta Aplicación conecta proveedor, trabajadores y administración en un flujo claro: publicación semanal,
                  selección por día y reportes listos para decidir qué preparar.
                </p>
              </div>

              <div className="flex flex-col gap-3 sm:flex-row">
                <Link href={primaryAction.href} className="btn btn-primary btn-lg rounded-full px-7">
                  {primaryAction.label}
                </Link>
                <a href="#modulos" className="btn btn-ghost btn-lg rounded-full border border-base-300 bg-base-100 px-7">
                  Explorar módulos
                </a>
              </div>

              <div className="grid gap-4 md:grid-cols-3">
                {[
                  { label: 'Publicación', value: 'Semanal', hint: 'Con control por día y estado' },
                  { label: 'Selección', value: '1 por día', hint: 'Visible y fácil de modificar' },
                  { label: 'Resumen', value: 'En tiempo real', hint: 'Totales claros para confección' },
                ].map((item) => (
                  <div key={item.label} className="soft-surface p-5">
                    <p className="text-sm font-medium text-base-content/52">{item.label}</p>
                    <p className="mt-3 font-display text-2xl font-semibold text-base-content">{item.value}</p>
                    <p className="mt-2 text-sm leading-6 text-base-content/54">{item.hint}</p>
                  </div>
                ))}
              </div>
            </section>

            <section className="panel-card overflow-hidden">
              <div className="border-b border-base-300/70 px-6 py-5 sm:px-8">
                <p className="section-kicker">Semana activa</p>
                <div className="mt-3 flex items-center justify-between gap-4">
                  <div>
                    <h2 className="font-display text-3xl font-semibold text-base-content">Operación lista para servicio</h2>
                    <p className="mt-2 text-sm leading-6 text-base-content/58">Publicación semanal, selección visible y consolidación diaria para cocina y administración.</p>
                  </div>
                  <span className="badge badge-success rounded-full border-none px-4 py-3 text-success-content">Publicada</span>
                </div>
              </div>

              <div className="space-y-4 px-6 py-6 sm:px-8">
                {[
                  { day: 'Lunes 24-03-2026', menus: 3, state: 'Publicado' },
                  { day: 'Martes 25-03-2026', menus: 2, state: 'Publicado' },
                  { day: 'Miércoles 26-03-2026', menus: 3, state: 'Publicado' },
                  { day: 'Jueves 27-03-2026', menus: 2, state: 'Borrador' },
                ].map((item) => (
                  <div key={item.day} className="flex items-center justify-between rounded-[22px] border border-base-300/70 bg-base-100 px-4 py-4">
                    <div>
                      <p className="font-semibold text-base-content">{item.day}</p>
                      <p className="mt-1 text-sm text-base-content/55">{item.menus} alternativas configuradas</p>
                    </div>
                    <span className={`badge rounded-full px-4 py-3 ${item.state === 'Publicado' ? 'badge-primary' : 'badge-outline'}`}>
                      {item.state}
                    </span>
                  </div>
                ))}
              </div>
            </section>
          </main>

          <section id="modulos" className="pb-10">
            <div className="mb-6">
              <p className="section-kicker">Módulos clave</p>
              <h2 className="mt-3 font-display text-3xl font-semibold text-base-content sm:text-4xl">
                Un layout claro para cada rol, con foco en la acción correcta.
              </h2>
            </div>

            <div className="grid gap-5 lg:grid-cols-3">
              {featureCards.map((card) => (
                <article key={card.title} className="panel-card p-6">
                  <span className="badge badge-outline rounded-full border-base-300/80 px-4 py-3">{card.caption}</span>
                  <h3 className="mt-5 font-display text-2xl font-semibold text-base-content">{card.title}</h3>
                  <p className="mt-3 text-sm leading-7 text-base-content/62">{card.description}</p>
                </article>
              ))}
            </div>
          </section>
        </div>
      </div>
    </>
  );
}
