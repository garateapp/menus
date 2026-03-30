import CalendarDrawer from '@/Components/calendar/CalendarDrawer';
import CalendarToolbar from '@/Components/calendar/CalendarToolbar';
import ImageUploadPreview from '@/Components/ImageUploadPreview';
import InputField from '@/Components/forms/InputField';
import PageHeader from '@/Components/PageHeader';
import SelectField from '@/Components/forms/SelectField';
import TextAreaField from '@/Components/forms/TextAreaField';
import ToggleField from '@/Components/forms/ToggleField';
import StatusBadge from '@/Components/StatusBadge';
import AppLayout from '@/Layouts/AppLayout';
import { formatDateInputValue, formatDisplayDate } from '@/lib/format-date';
import type { CalendarCell, CalendarRange, DailyMenu, MenuOption, WeeklyMenu } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

type WeekDefaults = {
  week_start_date: string;
  week_end_date: string;
} | null;

type Props = {
  calendar: CalendarRange;
  selectedDate?: string | null;
  selectedWeeklyMenu?: WeeklyMenu | null;
  selectedDailyMenu?: DailyMenu | null;
  selectedWeekDefaults?: WeekDefaults;
};

const statusOptions = [
  { label: 'Borrador', value: 'draft' },
  { label: 'Publicado', value: 'published' },
  { label: 'Cerrado', value: 'closed' },
];

function buildSupplierCalendarHref(calendar: CalendarRange, selectedDate?: string | null) {
  return route('supplier.weekly-menus.index', {
    view: calendar.view,
    date: calendar.focus_date,
    ...(selectedDate ? { selected_date: selectedDate } : {}),
  });
}

function buildWeekTitle(defaults: WeekDefaults) {
  if (!defaults) {
    return '';
  }

  return `Semana del ${formatDisplayDate(defaults.week_start_date)} al ${formatDisplayDate(defaults.week_end_date)}`;
}

function SupplierCalendarCell({ calendar, cell, selectedDate }: { calendar: CalendarRange; cell: CalendarCell; selectedDate?: string | null }) {
  const isSelected = selectedDate === cell.date;

  return (
    <Link
      href={buildSupplierCalendarHref(calendar, cell.date)}
      className={`flex min-h-[165px] flex-col rounded-[24px] border p-4 text-left transition-all duration-200 ${
        isSelected
          ? 'border-primary bg-primary/6 shadow-[0_18px_36px_-26px_rgba(59,130,246,0.6)]'
          : 'border-base-300 bg-base-100 hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-[0_18px_34px_-24px_rgba(15,23,42,0.18)]'
      } ${cell.is_current_month ? '' : 'opacity-55'}`}
    >
      <div className="flex items-start justify-between gap-3">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.18em] text-base-content/42">{cell.day_name}</p>
          <p className={`mt-2 font-display text-2xl font-semibold ${cell.is_today ? 'text-primary' : 'text-base-content'}`}>
            {cell.day_number}
          </p>
        </div>
        {cell.status ? <StatusBadge status={cell.status} /> : <span className="badge badge-ghost">Sin semana</span>}
      </div>

      <div className="mt-auto space-y-2 pt-5 text-sm text-base-content/62">
        <p className="min-h-[2.5rem]">{cell.title ?? 'Sin semana configurada'}</p>
        <p>{cell.has_day ? `${cell.options_count ?? 0} alternativas configuradas` : (cell.has_week ? 'Día sin configurar' : 'Crear semana')}</p>
      </div>
    </Link>
  );
}

function WeeklyMenuFormSection({
  weeklyMenu,
  defaults,
  redirectTo,
}: {
  weeklyMenu?: WeeklyMenu | null;
  defaults: WeekDefaults;
  redirectTo: string;
}) {
  const form = useForm({
    title: weeklyMenu?.title ?? buildWeekTitle(defaults),
    week_start_date: formatDateInputValue(weeklyMenu?.week_start_date ?? defaults?.week_start_date ?? ''),
    week_end_date: formatDateInputValue(weeklyMenu?.week_end_date ?? defaults?.week_end_date ?? ''),
    status: weeklyMenu?.status ?? 'draft',
    notes: weeklyMenu?.notes ?? '',
    redirect_to: redirectTo,
  });

  const submit = () => {
    if (weeklyMenu) {
      form.patch(route('supplier.weekly-menus.update', weeklyMenu.id));
      return;
    }

    form.post(route('supplier.weekly-menus.store'));
  };

  return (
    <div className="panel-card">
      <div className="card-body">
        <div className="mb-4">
          <p className="section-kicker">Semana</p>
          <h3 className="mt-2 text-xl font-semibold">{weeklyMenu ? 'Editar semana' : 'Crear semana'}</h3>
        </div>

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
            options={statusOptions}
          />
          <InputField label="Inicio de semana" name="week_start_date" type="date" value={form.data.week_start_date} onChange={(value) => form.setData('week_start_date', value)} error={form.errors.week_start_date} />
          <InputField label="Fin de semana" name="week_end_date" type="date" value={form.data.week_end_date} onChange={(value) => form.setData('week_end_date', value)} error={form.errors.week_end_date} />
          <div className="md:col-span-2">
            <TextAreaField label="Notas" name="notes" value={form.data.notes} onChange={(value) => form.setData('notes', value)} error={form.errors.notes} />
          </div>
          <div className="md:col-span-2 flex justify-end">
            <button type="submit" className="btn btn-primary rounded-full px-5" disabled={form.processing}>
              {weeklyMenu ? 'Guardar semana' : 'Crear semana'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function DailyMenuFormSection({
  selectedDate,
  weeklyMenu,
  dailyMenu,
  redirectTo,
}: {
  selectedDate: string;
  weeklyMenu: WeeklyMenu;
  dailyMenu?: DailyMenu | null;
  redirectTo: string;
}) {
  const form = useForm({
    menu_date: formatDateInputValue(dailyMenu?.menu_date ?? selectedDate),
    status: dailyMenu?.status ?? 'draft',
    redirect_to: redirectTo,
  });

  const submit = () => {
    if (dailyMenu) {
      form.patch(route('supplier.daily-menus.update', dailyMenu.id));
      return;
    }

    form.post(route('supplier.daily-menus.store', weeklyMenu.id));
  };

  return (
    <div className="panel-card">
      <div className="card-body">
        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
          <div>
            <p className="section-kicker">Día</p>
            <h3 className="mt-2 text-xl font-semibold">{dailyMenu ? 'Editar día' : 'Crear día'}</h3>
          </div>
          {dailyMenu ? <StatusBadge status={dailyMenu.status} /> : null}
        </div>

        <form
          className="grid gap-4 md:grid-cols-2"
          onSubmit={(event) => {
            event.preventDefault();
            submit();
          }}
        >
          <InputField label="Fecha" name="menu_date" type="date" value={form.data.menu_date} onChange={(value) => form.setData('menu_date', value)} error={form.errors.menu_date} />
          <SelectField
            label="Estado"
            name="status"
            value={form.data.status}
            onChange={(value) => form.setData('status', value as 'draft' | 'published' | 'closed')}
            error={form.errors.status}
            options={statusOptions}
          />
          <div className="md:col-span-2 flex justify-end">
            <button type="submit" className="btn btn-secondary rounded-full px-5" disabled={form.processing}>
              {dailyMenu ? 'Guardar día' : 'Crear día'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function MenuOptionEditor({
  option,
  redirectTo,
}: {
  option: MenuOption;
  redirectTo: string;
}) {
  const initialPreview = option.image_path ? `/storage/${option.image_path}` : null;
  const [preview, setPreview] = useState<string | null>(initialPreview);
  const form = useForm({
    title: option.title,
    description: option.description ?? '',
    quota: option.quota ? String(option.quota) : '',
    is_visible: option.is_visible,
    sort_order: String(option.sort_order ?? 0),
    image: null as File | null,
    redirect_to: redirectTo,
  });

  return (
    <div className="rounded-[24px] border border-base-300 bg-base-100 p-4">
      <div className="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
          <p className="font-semibold text-base-content">{option.title}</p>
          <p className="mt-1 text-sm text-base-content/58">{option.description || 'Sin descripción'}</p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <span className={`badge ${option.is_visible ? 'badge-secondary' : 'badge-outline'}`}>{option.is_visible ? 'Visible' : 'Oculta'}</span>
          <span className="badge badge-outline">{option.selections_count ?? option.selections?.length ?? 0} selecciones</span>
        </div>
      </div>

      <form
        className="grid gap-4 md:grid-cols-2"
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
          <button type="submit" className="btn btn-outline rounded-full px-5" disabled={form.processing}>
            Guardar alternativa
          </button>
        </div>
      </form>
    </div>
  );
}

function CreateMenuOptionForm({
  dailyMenu,
  redirectTo,
}: {
  dailyMenu: DailyMenu;
  redirectTo: string;
}) {
  const [preview, setPreview] = useState<string | null>(null);
  const form = useForm({
    title: '',
    description: '',
    quota: '',
    is_visible: true,
    sort_order: '0',
    image: null as File | null,
    redirect_to: redirectTo,
  });

  return (
    <div className="panel-card">
      <div className="card-body">
        <div className="mb-4">
          <p className="section-kicker">Nueva alternativa</p>
          <h3 className="mt-2 text-xl font-semibold">Agregar opción al día</h3>
        </div>

        <form
          className="grid gap-4 md:grid-cols-2"
          onSubmit={(event) => {
            event.preventDefault();
            form.post(route('supplier.menu-options.store', dailyMenu.id), {
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
            <button type="submit" className="btn btn-primary rounded-full px-5" disabled={form.processing}>
              Agregar alternativa
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function Index({
  calendar,
  selectedDate,
  selectedWeeklyMenu,
  selectedDailyMenu,
  selectedWeekDefaults,
}: Props) {
  const closeHref = buildSupplierCalendarHref(calendar);
  const redirectTo = buildSupplierCalendarHref(calendar, selectedDate);
  const selectedOptions = selectedDailyMenu?.menu_options ?? selectedDailyMenu?.menuOptions ?? [];

  return (
    <AppLayout>
      <PageHeader
        title="Calendario operativo"
        description="Gestiona semanas, crea días y edita alternativas directamente desde la vista mensual, semanal o diaria."
        action={{ label: 'Crear semana clásica', href: route('supplier.weekly-menus.create') }}
      />

      <CalendarToolbar routeName="supplier.weekly-menus.index" calendar={calendar} />

      <div className={`grid gap-4 ${calendar.view === 'day' ? 'grid-cols-1' : 'md:grid-cols-2 xl:grid-cols-7'}`}>
        {calendar.cells.map((cell) => (
          <SupplierCalendarCell key={cell.date} calendar={calendar} cell={cell} selectedDate={selectedDate} />
        ))}
      </div>

      <CalendarDrawer
        open={Boolean(selectedDate)}
        closeHref={closeHref}
        title={selectedDate ? `Gestión del ${formatDisplayDate(selectedDate)}` : 'Gestión del día'}
        description="Configura semana, día y alternativas sin salir del calendario."
      >
        {!selectedDate ? null : (
          <div className="space-y-5">
            <WeeklyMenuFormSection weeklyMenu={selectedWeeklyMenu} defaults={selectedWeekDefaults ?? null} redirectTo={redirectTo} />

            {selectedWeeklyMenu ? (
              <DailyMenuFormSection
                selectedDate={selectedDate}
                weeklyMenu={selectedWeeklyMenu}
                dailyMenu={selectedDailyMenu}
                redirectTo={redirectTo}
              />
            ) : null}

            {selectedDailyMenu ? (
              <>
                <div className="panel-card">
                  <div className="card-body">
                    <div className="mb-4 flex items-center justify-between gap-3">
                      <div>
                        <p className="section-kicker">Alternativas</p>
                        <h3 className="mt-2 text-xl font-semibold">Opciones configuradas</h3>
                      </div>
                      <span className="badge badge-outline">{selectedOptions.length} registradas</span>
                    </div>

                    <div className="space-y-4">
                      {selectedOptions.map((option) => (
                        <MenuOptionEditor key={option.id} option={option} redirectTo={redirectTo} />
                      ))}
                    </div>
                  </div>
                </div>

                <CreateMenuOptionForm dailyMenu={selectedDailyMenu} redirectTo={redirectTo} />
              </>
            ) : null}
          </div>
        )}
      </CalendarDrawer>
    </AppLayout>
  );
}
