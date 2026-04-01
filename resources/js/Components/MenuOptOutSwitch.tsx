import type { MenuOption } from '@/types';
import { useForm } from '@inertiajs/react';

type Props = {
  dailyMenuId: number;
  option: MenuOption;
  selected: boolean;
  disabled?: boolean;
};

export default function MenuOptOutSwitch({ dailyMenuId, option, selected, disabled = false }: Props) {
  const form = useForm({
    menu_option_id: option.id,
  });

  const isDisabled = disabled || form.processing || selected;

  return (
    <div className="panel-card border-warning/30 bg-warning/5">
      <div className="card-body gap-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="space-y-2">
            <div className="flex flex-wrap items-center gap-2">
              <span className="badge badge-warning badge-outline">Opción rápida</span>
              {selected ? <span className="badge badge-success">Seleccionada</span> : null}
            </div>
            <h3 className="text-xl font-semibold">{option.title}</h3>
            <p className="max-w-2xl text-sm leading-6 text-base-content/65">
              Si activas este switch, quedará registrada tu decisión de no solicitar almuerzo para esta fecha.
            </p>
            {selected ? (
              <p className="text-sm font-medium text-success">
                Ya informaste que no solicitarás menú. Si cambias de opinión, elige una alternativa más abajo.
              </p>
            ) : null}
          </div>

          <label className={`flex items-center gap-4 ${isDisabled ? 'opacity-80' : 'cursor-pointer'}`}>
            <span className="text-sm font-medium text-base-content/70">No solicitaré menú</span>
            <input
              type="checkbox"
              className="toggle toggle-warning toggle-lg"
              checked={selected}
              disabled={isDisabled}
              onChange={() => form.post(route('worker.selections.store', dailyMenuId))}
            />
          </label>
        </div>
      </div>
    </div>
  );
}
