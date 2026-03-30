import PageHeader from '@/Components/PageHeader';
import StatCard from '@/Components/StatCard';
import AppLayout from '@/Layouts/AppLayout';

export default function Index({ summary }: { summary: Record<string, number> }) {
  return (
    <AppLayout>
      <PageHeader title="Reportes globales" description="Resumen consolidado del sistema." />
      <div className="grid gap-4 md:grid-cols-3">
        <StatCard label="Selecciones totales" value={summary.totalSelections} />
        <StatCard label="Semanas registradas" value={summary.weeklyMenus} />
        <StatCard label="Selecciones hoy" value={summary.todaySelections} />
      </div>
    </AppLayout>
  );
}
