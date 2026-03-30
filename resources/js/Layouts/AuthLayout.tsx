import { Head, Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

type Props = PropsWithChildren<{
  title: string;
  subtitle: string;
}>;

export default function AuthLayout({ title, subtitle, children }: Props) {
  return (
    <>
      <Head title={title} />

      <div data-theme="emerald" className="relative min-h-screen overflow-hidden bg-base-200">
        <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.12),transparent_24%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.12),transparent_24%)]" />

        <div className="relative mx-auto flex min-h-screen max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
          <div className="grid w-full items-stretch gap-6 lg:grid-cols-[1.15fr_0.85fr]">
            <section className="glass-surface hidden flex-col justify-between p-10 lg:flex xl:p-12">
              <div>
                <p className="section-kicker">Packing Garate</p>
                <div className="mt-3 flex items-center gap-3">
                  <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <svg viewBox="0 0 24 24" className="h-6 w-6 fill-none stroke-current stroke-2" aria-hidden="true">
                      <path d="M4 12h16M7 7h10M7 17h10" strokeLinecap="round" />
                    </svg>
                  </div>
                  <div>
                    <h1 className="font-display text-3xl font-semibold text-base-content">Menú</h1>
                    <p className="text-sm text-base-content/55">Gestión semanal de almuerzos</p>
                  </div>
                </div>

                <div className="mt-10 max-w-2xl">
                  <h2 className="font-display text-5xl font-semibold leading-tight text-base-content">
                    Un acceso simple a una operación de colaciones mucho más ordenada.
                  </h2>
                  <p className="mt-5 text-base leading-7 text-base-content/66">
                    El proveedor publica alternativas por día, los trabajadores seleccionan su menú y el equipo
                    obtiene una vista clara para confección y control diario.
                  </p>
                </div>
              </div>

              <div className="grid gap-4 md:grid-cols-3">
                {[
                  { title: 'Publicación', value: 'Semanal', hint: 'Con estados por semana y día' },
                  { title: 'Selección', value: '1 opción', hint: 'Editable mientras siga abierta' },
                  { title: 'Resumen', value: 'En vivo', hint: 'Totales útiles para cocina y gestión' },
                ].map((item) => (
                  <div key={item.title} className="soft-surface p-4">
                    <p className="text-sm font-medium text-base-content/55">{item.title}</p>
                    <p className="mt-3 font-display text-2xl font-semibold text-base-content">{item.value}</p>
                    <p className="mt-2 text-sm leading-6 text-base-content/52">{item.hint}</p>
                  </div>
                ))}
              </div>
            </section>

            <section className="panel-card flex items-center justify-center p-3 sm:p-5">
              <div className="w-full max-w-md rounded-[26px] bg-base-100 px-6 py-8 sm:px-8">
                <div className="mb-8">
                  <Link href="/" className="inline-flex items-center gap-3 text-sm font-semibold text-primary">
                    <span className="flex h-9 w-9 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                      <svg viewBox="0 0 24 24" className="h-4 w-4 fill-none stroke-current stroke-2" aria-hidden="true">
                        <path d="M4 12h16M7 7h10M7 17h10" strokeLinecap="round" />
                      </svg>
                    </span>
                    <span>Menú</span>
                  </Link>

                  <h2 className="mt-6 font-display text-3xl font-semibold text-base-content">{title}</h2>
                  <p className="mt-3 text-sm leading-6 text-base-content/62">{subtitle}</p>
                </div>

                {children}
              </div>
            </section>
          </div>
        </div>
      </div>
    </>
  );
}
