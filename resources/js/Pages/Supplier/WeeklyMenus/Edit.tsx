import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import WeeklyMenuForm from '@/Pages/Supplier/WeeklyMenus/Form';
import type { WeeklyMenu } from '@/types';

export default function Edit({ weeklyMenu }: { weeklyMenu: WeeklyMenu }) {
  return (
    <AppLayout>
      <PageHeader title="Editar semana" description="Actualiza las fechas, el estado o las notas." />
      <div className="panel-card">
        <div className="card-body">
          <WeeklyMenuForm weeklyMenu={weeklyMenu} submitLabel="Guardar cambios" submitRoute={route('supplier.weekly-menus.update', weeklyMenu.id)} method="patch" />
        </div>
      </div>
    </AppLayout>
  );
}
