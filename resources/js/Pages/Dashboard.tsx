import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard() {
  return (
    <AuthenticatedLayout
      header={(
        <div>
          <p className="section-kicker">Resumen</p>
          <h2 className="mt-2 font-display text-3xl font-semibold text-base-content">Dashboard</h2>
        </div>
      )}
    >
      <Head title="Dashboard" />

      <div className="panel-card p-6 text-base-content/72">
        Has iniciado sesión correctamente.
      </div>
    </AuthenticatedLayout>
  );
}
