import PageHeader from '@/Components/PageHeader';
import StatCard from '@/Components/StatCard';
import AppLayout from '@/Layouts/AppLayout';

export default function Dashboard({ stats }: { stats: Record<string, number> }) {
  return (
    <AppLayout>
      <PageHeader title="Panel SuperAdmin" description="Supervisa usuarios, menús y estado general del sistema." />
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <StatCard label="Usuarios" value={stats.totalUsers} />
        <StatCard label="Trabajadores" value={stats.totalWorkers} />
        <StatCard label="Proveedores" value={stats.totalSuppliers} />
        <StatCard label="Semanas activas" value={stats.activeWeeks} />
        <StatCard label="Selecciones hoy" value={stats.todaySelections} />
      </div>
    </AppLayout>
  );
}
