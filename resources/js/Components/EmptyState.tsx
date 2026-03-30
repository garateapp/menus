type Props = {
  title: string;
  description: string;
};

export default function EmptyState({ title, description }: Props) {
  return (
    <div className="panel-card border-dashed">
      <div className="card-body items-center py-12 text-center">
        <h3 className="text-xl font-semibold">{title}</h3>
        <p className="max-w-md text-sm text-base-content/70">{description}</p>
      </div>
    </div>
  );
}
