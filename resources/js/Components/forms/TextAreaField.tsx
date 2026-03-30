type Props = {
  label: string;
  name: string;
  value: string;
  onChange: (value: string) => void;
  error?: string;
  rows?: number;
};

export default function TextAreaField({ label, name, value, onChange, error, rows = 4 }: Props) {
  return (
    <label className="form-control w-full">
      <span className="label-text mb-2 font-medium">{label}</span>
      <textarea
        name={name}
        rows={rows}
        value={value}
        onChange={(event) => onChange(event.target.value)}
        className={`textarea textarea-bordered w-full ${error ? 'textarea-error' : ''}`}
      />
      {error ? <span className="mt-2 text-sm text-error">{error}</span> : null}
    </label>
  );
}
