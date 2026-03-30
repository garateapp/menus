import type { MenuStatus } from '@/types';

const classes: Record<MenuStatus, string> = {
  draft: 'badge-ghost',
  published: 'badge-success',
  closed: 'badge-error',
};

const labels: Record<MenuStatus, string> = {
  draft: 'Borrador',
  published: 'Publicado',
  closed: 'Cerrado',
};

export default function StatusBadge({ status }: { status: MenuStatus }) {
  return <span className={`badge ${classes[status]}`}>{labels[status]}</span>;
}
