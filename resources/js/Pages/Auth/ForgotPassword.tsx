import InputField from '@/Components/forms/InputField';
import AuthLayout from '@/Layouts/AuthLayout';
import { Head, useForm } from '@inertiajs/react';

export default function ForgotPassword({ status }: { status?: string }) {
  const form = useForm({
    email: '',
  });

  return (
    <AuthLayout title="Recuperar contraseña" subtitle="Te enviaremos un enlace para restablecerla.">
      <Head title="Recuperar contraseña" />
      {status ? <div className="alert alert-success">{status}</div> : null}
      <form
        className="space-y-4"
        onSubmit={(event) => {
          event.preventDefault();
          form.post(route('password.email'));
        }}
      >
        <InputField label="Correo" name="email" type="email" value={form.data.email} onChange={(value) => form.setData('email', value)} error={form.errors.email} />
        <button type="submit" className="btn btn-primary w-full" disabled={form.processing}>
          Enviar enlace
        </button>
      </form>
    </AuthLayout>
  );
}
