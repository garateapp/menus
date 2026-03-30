import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import UserForm from '@/Pages/SuperAdmin/Users/Form';
import type { RoleName } from '@/types';

export default function Create({ roles }: { roles: RoleName[] }) {
  return (
    <AppLayout>
      <PageHeader title="Crear usuario" description="Crea cuentas internas y asigna su rol inicial." />
      <div className="panel-card">
        <div className="card-body">
          <UserForm roles={roles} submitRoute={route('superadmin.users.store')} submitLabel="Crear usuario" />
        </div>
      </div>
    </AppLayout>
  );
}
