export default function SelectionBadge({ selected }: { selected: boolean }) {
  return (
    <span className={`badge ${selected ? 'badge-primary' : 'badge-outline'}`}>
      {selected ? 'Tu elección' : 'Disponible'}
    </span>
  );
}
