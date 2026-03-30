type Props = {
  label: string;
  value: string | number;
  hint?: string;
};

export default function StatCard({ label, value, hint }: Props) {
  return (
    <div className="panel-card">
      <div className="card-body gap-4">
        <div className="flex items-center justify-between gap-3">
          <p className="text-sm font-medium text-base-content/55">{label}</p>
          <span className="h-2.5 w-2.5 rounded-full bg-primary/70" />
        </div>
        <p className="font-display text-3xl font-semibold tracking-tight text-base-content sm:text-4xl">{value}</p>
        {hint ? <p className="text-sm leading-6 text-base-content/52">{hint}</p> : null}
      </div>
    </div>
  );
}
