import SelectionBadge from '@/Components/SelectionBadge';
import type { MenuOption } from '@/types';
import { Link, useForm } from '@inertiajs/react';

type Props = {
  dailyMenuId: number;
  option: MenuOption;
  selected: boolean;
  disabled?: boolean;
};

export default function MenuOptionCard({ dailyMenuId, option, selected, disabled = false }: Props) {
  const form = useForm({
    menu_option_id: option.id,
  });

  const imageUrl = option.image_path ? `/storage/${option.image_path}` : null;
  const remainingQuota = option.remaining_quota ?? (option.quota !== null && option.quota !== undefined
    ? Math.max(option.quota - (option.selections_count ?? 0), 0)
    : null);
  const isSoldOut = option.is_sold_out ?? (remainingQuota !== null && remainingQuota <= 0);
  const isDisabled = disabled || form.processing || (isSoldOut && !selected);
  const actionLabel = isSoldOut && !selected ? 'Sin cupos' : (selected ? 'Cambiar selección' : 'Elegir opción');
  const quotaLabel = option.quota === null || option.quota === undefined
    ? 'Cupos disponibles: Sin límite'
    : `Cupos disponibles: ${remainingQuota ?? 0} de ${option.quota}`;

  return (
    <div className="panel-card overflow-hidden">
      {imageUrl ? <img src={imageUrl} alt={option.title} className="h-48 w-full object-cover" /> : null}
      <div className="card-body gap-4">
        <div className="flex items-start justify-between gap-3">
          <div>
            <h3 className="text-xl font-semibold">{option.title}</h3>
            <p className="text-sm text-base-content/70">{option.description || 'Sin descripción adicional.'}</p>
          </div>
          <SelectionBadge selected={selected} />
        </div>

        <div className="flex items-center gap-2 text-xs text-base-content/60">
          <span>{quotaLabel}</span>
          <span>•</span>
          <span>{option.is_visible ? 'Visible' : 'Oculta'}</span>
        </div>

        <div className="card-actions justify-end">
          <button
            type="button"
            disabled={isDisabled}
            className={`btn ${selected ? 'btn-secondary' : 'btn-primary'}`}
            onClick={() => form.post(route('worker.selections.store', dailyMenuId))}
          >
            {actionLabel}
          </button>
        </div>
      </div>
    </div>
  );
}
