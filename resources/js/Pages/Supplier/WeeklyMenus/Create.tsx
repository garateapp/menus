import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import WeeklyMenuForm from '@/Pages/Supplier/WeeklyMenus/Form';

export default function Create() {
  return (
    <AppLayout>
      <PageHeader title="Crear semana" description="Define rango, estado y notas iniciales del menú semanal." />
      <div className="panel-card">
        <div className="card-body">
          <WeeklyMenuForm submitLabel="Crear semana" submitRoute={route('supplier.weekly-menus.store')} />
        </div>
      </div>
    </AppLayout>
  );
}
