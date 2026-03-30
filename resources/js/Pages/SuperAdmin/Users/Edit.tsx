import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import UserForm from '@/Pages/SuperAdmin/Users/Form';
import type { RoleName, User } from '@/types';

export default function Edit({ user, roles }: { user: User; roles: RoleName[] }) {
  return (
    <AppLayout>
      <PageHeader title={`Editar ${user.name}`} description="Actualiza datos, rol y estado del usuario." />
      <div className="panel-card">
        <div className="card-body">
          <UserForm user={user} roles={roles} submitRoute={route('superadmin.users.update', user.id)} submitLabel="Guardar cambios" method="patch" />
        </div>
      </div>
    </AppLayout>
  );
}
