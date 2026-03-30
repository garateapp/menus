type Option = {
  label: string;
  value: string;
};

type Props = {
  label: string;
  name: string;
  value: string;
  onChange: (value: string) => void;
  options: Option[];
  error?: string;
};

export default function SelectField({ label, name, value, onChange, options, error }: Props) {
  return (
    <label className="form-control w-full">
      <span className="label-text mb-2 font-medium">{label}</span>
      <select
        name={name}
        value={value}
        onChange={(event) => onChange(event.target.value)}
        className={`select select-bordered w-full ${error ? 'select-error' : ''}`}
      >
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
      {error ? <span className="mt-2 text-sm text-error">{error}</span> : null}
    </label>
  );
}
