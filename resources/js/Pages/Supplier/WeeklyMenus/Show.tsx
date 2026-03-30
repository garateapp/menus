import ImageUploadPreview from '@/Components/ImageUploadPreview';
import InputField from '@/Components/forms/InputField';
import PageHeader from '@/Components/PageHeader';
import SelectField from '@/Components/forms/SelectField';
import TextAreaField from '@/Components/forms/TextAreaField';
import ToggleField from '@/Components/forms/ToggleField';
import StatusBadge from '@/Components/StatusBadge';
import AppLayout from '@/Layouts/AppLayout';
import { formatDateInputValue, formatDisplayDate } from '@/lib/format-date';
import type { DailyMenu, MenuOption, WeeklyMenu } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

const statusOptions = [
  { label: 'Borrador', value: 'draft' },
  { label: 'Publicado', value: 'published' },
  { label: 'Cerrado', value: 'closed' },
];

function buildOptionImageUrl(imagePath?: string | null): string | null {
  return imagePath ? `/storage/${imagePath}` : null;
}

function MenuOptionEditorCard({ option }: { option: MenuOption }) {
  const initialPreview = buildOptionImageUrl(option.image_path);
  const [preview, setPreview] = useState<string | null>(initialPreview);
  const form = useForm({
    title: option.title,
    description: option.description ?? '',
    quota: option.quota ? String(option.quota) : '',
    is_visible: option.is_visible,
    sort_order: String(option.sort_order ?? 0),
    image: null as File | null,
  });

  return (
    <div className="rounded-2xl border border-base-300 bg-base-200">
      <div className="border-b border-base-300 px-4 py-3">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p className="font-semibold">{option.title}</p>
            <p className="text-sm text-base-content/60">{option.description || 'Sin descripción'}</p>
          </div>
          <div className="flex items-center gap-2">
            <span className={`badge ${option.is_visible ? 'badge-secondary' : 'badge-outline'}`}>
              {option.is_visible ? 'Visible' : 'Oculta'}
            </span>
            <span className="badge badge-outline">{option.selections_count ?? option.selections?.length ?? 0} selecciones</span>
          </div>
        </div>
      </div>

      <form
        className="grid gap-4 p-4 md:grid-cols-2"
        onSubmit={(event) => {
          event.preventDefault();
          form.transform((data) => ({
            ...data,
            _method: 'patch',
          }));
          form.post(route('supplier.menu-options.update', option.id), {
            forceFormData: true,
          });
        }}
      >
        <InputField label="Nombre del plato" name="title" value={form.data.title} onChange={(value) => form.setData('title', value)} error={form.errors.title} />
        <InputField label="Cupo" name="quota" value={form.data.quota} onChange={(value) => form.setData('quota', value)} error={form.errors.quota} placeholder="Opcional" />
        <div className="md:col-span-2">
          <TextAreaField label="Descripción" name="description" value={form.data.description} onChange={(value) => form.setData('description', value)} error={form.errors.description} />
        </div>
        <InputField label="Orden" name="sort_order" type="number" value={form.data.sort_order} onChange={(value) => form.setData('sort_order', value)} error={form.errors.sort_order} />
        <div className="flex items-end">
          <ToggleField label="Visible para trabajadores" checked={form.data.is_visible} onChange={(value) => form.setData('is_visible', value)} />
        </div>
        <div className="md:col-span-2">
          <input
            type="file"
            className="file-input file-input-bordered w-full"
            accept=".jpg,.jpeg,.png,.webp"
            onChange={(event) => {
              const file = event.target.files?.[0] ?? null;
              form.setData('image', file);
              setPreview(file ? URL.createObjectURL(file) : initialPreview);
            }}
          />
          {form.errors.image ? <span className="mt-2 block text-sm text-error">{form.errors.image}</span> : null}
        </div>
        <div className="md:col-span-2">
          <ImageUploadPreview previewUrl={preview} />
        </div>
        <div className="md:col-span-2 flex justify-end">
          <button type="submit" className="btn btn-secondary" disabled={form.processing}>
            Guardar alternativa
          </button>
        </div>
      </form>
    </div>
  );
}

function CreateMenuOptionForm({ day }: { day: DailyMenu }) {
  const [preview, setPreview] = useState<string | null>(null);
  const form = useForm({
    title: '',
    description: '',
    quota: '',
    is_visible: true,
    sort_order: '0',
    image: null as File | null,
  });

  return (
    <form
      className="grid gap-4 rounded-2xl border border-dashed border-base-300 bg-base-100 p-4 md:grid-cols-2"
      onSubmit={(event) => {
        event.preventDefault();
        form.post(route('supplier.menu-options.store', day.id), {
          forceFormData: true,
          onSuccess: () => {
            form.reset();
            setPreview(null);
          },
        });
      }}
    >
      <InputField label="Nombre del plato" name="title" value={form.data.title} onChange={(value) => form.setData('title', value)} error={form.errors.title} />
      <InputField label="Cupo" name="quota" value={form.data.quota} onChange={(value) => form.setData('quota', value)} error={form.errors.quota} placeholder="Opcional" />
      <div className="md:col-span-2">
        <TextAreaField label="Descripción" name="description" value={form.data.description} onChange={(value) => form.setData('description', value)} error={form.errors.description} />
      </div>
      <InputField label="Orden" name="sort_order" type="number" value={form.data.sort_order} onChange={(value) => form.setData('sort_order', value)} error={form.errors.sort_order} />
      <div className="flex items-end">
        <ToggleField label="Visible para trabajadores" checked={form.data.is_visible} onChange={(value) => form.setData('is_visible', value)} />
      </div>
      <div className="md:col-span-2">
        <input
          type="file"
          className="file-input file-input-bordered w-full"
          accept=".jpg,.jpeg,.png,.webp"
          onChange={(event) => {
            const file = event.target.files?.[0] ?? null;
            form.setData('image', file);
            setPreview(file ? URL.createObjectURL(file) : null);
          }}
        />
        {form.errors.image ? <span className="mt-2 block text-sm text-error">{form.errors.image}</span> : null}
      </div>
      <div className="md:col-span-2">
        <ImageUploadPreview previewUrl={preview} />
      </div>
      <div className="md:col-span-2 flex justify-end">
        <button type="submit" className="btn btn-primary" disabled={form.processing}>
          Agregar alternativa
        </button>
      </div>
    </form>
  );
}

function DayEditorCard({ day }: { day: DailyMenu }) {
  const form = useForm({
    menu_date: formatDateInputValue(day.menu_date),
    status: day.status,
  });

  const options = day.menu_options ?? [];

  return (
    <div className="panel-card">
      <div className="card-body gap-6">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 className="text-xl font-semibold">{formatDisplayDate(day.menu_date)}</h2>
            <div className="mt-2 flex items-center gap-2">
              <StatusBadge status={day.status} />
              <span className="text-sm text-base-content/60">{options.length} alternativas</span>
            </div>
          </div>
          <Link href={route('supplier.reports.daily', { date: day.menu_date })} className="btn btn-sm btn-outline">
            Ver reporte
          </Link>
        </div>

        <form
          className="grid gap-4 rounded-2xl border border-base-300 bg-base-100 p-4 md:grid-cols-2"
          onSubmit={(event) => {
            event.preventDefault();
            form.patch(route('supplier.daily-menus.update', day.id));
          }}
        >
          <InputField label="Fecha" name="menu_date" type="date" value={form.data.menu_date} onChange={(value) => form.setData('menu_date', value)} error={form.errors.menu_date} />
          <SelectField
            label="Estado del día"
            name="status"
            value={form.data.status}
            onChange={(value) => form.setData('status', value as 'draft' | 'published' | 'closed')}
            error={form.errors.status}
            options={statusOptions}
          />
          <div className="md:col-span-2 flex justify-end">
            <button type="submit" className="btn btn-accent" disabled={form.processing}>
              Guardar día
            </button>
          </div>
        </form>

        <div className="space-y-4">
          {options.map((option) => (
            <MenuOptionEditorCard key={option.id} option={option} />
          ))}
        </div>

        <CreateMenuOptionForm day={day} />
      </div>
    </div>
  );
}

export default function Show({ weeklyMenu }: { weeklyMenu: WeeklyMenu }) {
  const dayForm = useForm({
    menu_date: '',
    status: 'draft',
  });

  const days = (weeklyMenu.daily_menus ?? []) as DailyMenu[];

  return (
    <AppLayout>
      <PageHeader
        title={weeklyMenu.title}
        description={`${formatDisplayDate(weeklyMenu.week_start_date)} al ${formatDisplayDate(weeklyMenu.week_end_date)}`}
        extra={<StatusBadge status={weeklyMenu.status} />}
        action={{ label: 'Editar semana', href: route('supplier.weekly-menus.edit', weeklyMenu.id) }}
      />

      <div className="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <div className="panel-card">
          <div className="card-body">
            <h2 className="card-title">Agregar día</h2>
            <form
              className="space-y-4"
              onSubmit={(event) => {
                event.preventDefault();
                dayForm.post(route('supplier.daily-menus.store', weeklyMenu.id), {
                  onSuccess: () => dayForm.reset(),
                });
              }}
            >
              <InputField label="Fecha" name="menu_date" type="date" value={dayForm.data.menu_date} onChange={(value) => dayForm.setData('menu_date', value)} error={dayForm.errors.menu_date} />
              <SelectField
                label="Estado"
                name="status"
                value={dayForm.data.status}
                onChange={(value) => dayForm.setData('status', value as 'draft' | 'published' | 'closed')}
                error={dayForm.errors.status}
                options={statusOptions}
              />
              <button type="submit" className="btn btn-primary" disabled={dayForm.processing}>
                Agregar día
              </button>
            </form>
          </div>
        </div>

        <div className="space-y-6">
          {days.length ? (
            days.map((day) => <DayEditorCard key={day.id} day={day} />)
          ) : (
            <div className="panel-card">
              <div className="card-body text-sm text-base-content/70">
                Esta semana todavía no tiene días configurados.
              </div>
            </div>
          )}
        </div>
      </div>
    </AppLayout>
  );
}
