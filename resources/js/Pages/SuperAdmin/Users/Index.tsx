import DataTable from '@/Components/DataTable';
import PageHeader from '@/Components/PageHeader';
import Pagination from '@/Components/Pagination';
import AppLayout from '@/Layouts/AppLayout';
import type { PaginatedResponse, User } from '@/types';
import { Link } from '@inertiajs/react';

export default function Index({ users }: { users: PaginatedResponse<User> }) {
  return (
    <AppLayout>
      <PageHeader title="Usuarios" description="Activa, desactiva y reasigna roles de la plataforma." action={{ label: 'Crear usuario', href: route('superadmin.users.create') }} />
      <DataTable
        rows={users.data}
        columns={[
          { key: 'name', header: 'Nombre', render: (row) => row.name },
          { key: 'email', header: 'Correo', render: (row) => row.email },
          { key: 'role', header: 'Rol', render: (row) => row.roles?.[0]?.name ?? 'Sin rol' },
          { key: 'active', header: 'Activo', render: (row) => (row.is_active ? 'Sí' : 'No') },
          { key: 'actions', header: '', render: (row) => <Link className="btn btn-sm btn-outline" href={route('superadmin.users.edit', row.id)}>Editar</Link> },
        ]}
      />
      <Pagination links={users.links} />
    </AppLayout>
  );
}
