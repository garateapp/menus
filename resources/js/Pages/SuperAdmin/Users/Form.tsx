import InputField from '@/Components/forms/InputField';
import SelectField from '@/Components/forms/SelectField';
import ToggleField from '@/Components/forms/ToggleField';
import type { RoleName, User } from '@/types';
import { useForm } from '@inertiajs/react';

type Props = {
  user?: User;
  roles: RoleName[];
  submitRoute: string;
  submitLabel: string;
  method?: 'post' | 'patch';
};

export default function UserForm({ user, roles, submitRoute, submitLabel, method = 'post' }: Props) {
  const form = useForm({
    name: user?.name ?? '',
    email: user?.email ?? '',
    username: user?.username ?? '',
    password: '',
    password_confirmation: '',
    role: (user?.roles?.[0]?.name ?? 'Worker') as RoleName,
    is_active: user?.is_active ?? true,
  });

  const submit = () => {
    if (method === 'patch') {
      form.patch(submitRoute);
      return;
    }

    form.post(submitRoute);
  };

  return (
    <form
      className="grid gap-4 md:grid-cols-2"
      onSubmit={(event) => {
        event.preventDefault();
        submit();
      }}
    >
      <InputField label="Nombre completo" name="name" value={form.data.name} onChange={(value) => form.setData('name', value)} error={form.errors.name} />
      <InputField label="Correo" name="email" type="email" value={form.data.email} onChange={(value) => form.setData('email', value)} error={form.errors.email} />
      <InputField label="Username" name="username" value={form.data.username} onChange={(value) => form.setData('username', value)} error={form.errors.username} />
      <SelectField
        label="Rol"
        name="role"
        value={form.data.role}
        onChange={(value) => form.setData('role', value as RoleName)}
        error={form.errors.role}
        options={roles.map((role) => ({ label: role, value: role }))}
      />
      <InputField label="Contraseña" name="password" type="password" value={form.data.password} onChange={(value) => form.setData('password', value)} error={form.errors.password} />
      <InputField label="Confirmar contraseña" name="password_confirmation" type="password" value={form.data.password_confirmation} onChange={(value) => form.setData('password_confirmation', value)} error={form.errors.password_confirmation} />
      <div className="md:col-span-2">
        <ToggleField label="Usuario activo" checked={form.data.is_active} onChange={(value) => form.setData('is_active', value)} />
      </div>
      <div className="md:col-span-2">
        <button type="submit" className="btn btn-primary" disabled={form.processing}>
          {submitLabel}
        </button>
      </div>
    </form>
  );
}
