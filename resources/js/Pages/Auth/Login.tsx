import InputField from '@/Components/forms/InputField';
import AuthLayout from '@/Layouts/AuthLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({
  status,
  canResetPassword,
}: {
  status?: string;
  canResetPassword: boolean;
}) {
  const form = useForm({
    email: '',
    password: '',
    remember: false,
  });

  return (
    <AuthLayout title="Ingresar" subtitle="Accede con tu cuenta corporativa o tu contraseña.">
      <Head title="Ingresar" />

      {status ? <div className="alert alert-success">{status}</div> : null}

      <button
        type="button"
        className="btn btn-outline w-full"
        onClick={() => (window.location.href = route('auth.google.redirect'))}
      >
        Continuar con Google
      </button>

      <div className="divider text-xs uppercase">o con correo y contraseña</div>

      <form
        className="space-y-4"
        onSubmit={(event) => {
          event.preventDefault();
          form.post(route('login'), {
            onFinish: () => form.reset('password'),
          });
        }}
      >
        <InputField label="Correo" name="email" type="email" value={form.data.email} onChange={(value) => form.setData('email', value)} error={form.errors.email} />
        <InputField label="Contraseña" name="password" type="password" value={form.data.password} onChange={(value) => form.setData('password', value)} error={form.errors.password} />

        <label className="label cursor-pointer justify-start gap-3">
          <input
            type="checkbox"
            className="checkbox checkbox-primary"
            checked={form.data.remember}
            onChange={(event) => form.setData('remember', event.target.checked)}
          />
          <span className="label-text">Mantener sesión iniciada</span>
        </label>

        <button type="submit" className="btn btn-primary w-full" disabled={form.processing}>
          Entrar
        </button>
      </form>

      {canResetPassword ? (
        <Link href={route('password.request')} className="text-sm text-primary underline underline-offset-4">
          ¿Olvidaste tu contraseña?
        </Link>
      ) : null}
    </AuthLayout>
  );
}
