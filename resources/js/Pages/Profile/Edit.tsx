import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({
  mustVerifyEmail,
  status,
}: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
  return (
    <AuthenticatedLayout
      header={(
        <div>
          <p className="section-kicker">Cuenta</p>
          <h2 className="mt-2 font-display text-3xl font-semibold text-base-content">Mi perfil</h2>
        </div>
      )}
    >
      <Head title="Profile" />

      <div className="grid gap-6">
        <div className="panel-card p-6 sm:p-8">
          <UpdateProfileInformationForm
            mustVerifyEmail={mustVerifyEmail}
            status={status}
            className="max-w-2xl"
          />
        </div>

        <div className="panel-card p-6 sm:p-8">
          <UpdatePasswordForm className="max-w-2xl" />
        </div>

        <div className="panel-card border-error/20 p-6 sm:p-8">
          <DeleteUserForm className="max-w-2xl" />
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
