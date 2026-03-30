import InputField from '@/Components/forms/InputField';
import SelectField from '@/Components/forms/SelectField';
import TextAreaField from '@/Components/forms/TextAreaField';
import { formatDateInputValue } from '@/lib/format-date';
import type { WeeklyMenu } from '@/types';
import { useForm } from '@inertiajs/react';

type Props = {
  weeklyMenu?: WeeklyMenu;
  submitLabel: string;
  submitRoute: string;
  method?: 'post' | 'patch';
};

export default function WeeklyMenuForm({ weeklyMenu, submitLabel, submitRoute, method = 'post' }: Props) {
  const form = useForm({
    title: weeklyMenu?.title ?? '',
    week_start_date: formatDateInputValue(weeklyMenu?.week_start_date),
    week_end_date: formatDateInputValue(weeklyMenu?.week_end_date),
    status: weeklyMenu?.status ?? 'draft',
    notes: weeklyMenu?.notes ?? '',
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
      <InputField label="Título" name="title" value={form.data.title} onChange={(value) => form.setData('title', value)} error={form.errors.title} />
      <SelectField
        label="Estado"
        name="status"
        value={form.data.status}
        onChange={(value) => form.setData('status', value as 'draft' | 'published' | 'closed')}
        error={form.errors.status}
        options={[
          { label: 'Borrador', value: 'draft' },
          { label: 'Publicado', value: 'published' },
          { label: 'Cerrado', value: 'closed' },
        ]}
      />
      <InputField label="Inicio de semana" name="week_start_date" type="date" value={form.data.week_start_date} onChange={(value) => form.setData('week_start_date', value)} error={form.errors.week_start_date} />
      <InputField label="Fin de semana" name="week_end_date" type="date" value={form.data.week_end_date} onChange={(value) => form.setData('week_end_date', value)} error={form.errors.week_end_date} />
      <div className="md:col-span-2">
        <TextAreaField label="Notas" name="notes" value={form.data.notes} onChange={(value) => form.setData('notes', value)} error={form.errors.notes} />
      </div>
      <div className="md:col-span-2">
        <button type="submit" className="btn btn-primary" disabled={form.processing}>
          {submitLabel}
        </button>
      </div>
    </form>
  );
}
