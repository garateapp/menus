type Props = {
  label: string;
  name: string;
  type?: string;
  value: string | number;
  onChange: (value: string) => void;
  error?: string;
  placeholder?: string;
};

export default function InputField({ label, name, type = 'text', value, onChange, error, placeholder }: Props) {
  return (
    <label className="form-control w-full">
      <span className="label-text mb-2 font-medium">{label}</span>
      <input
        name={name}
        type={type}
        value={value}
        placeholder={placeholder}
        onChange={(event) => onChange(event.target.value)}
        className={`input input-bordered w-full ${error ? 'input-error' : ''}`}
      />
      {error ? <span className="mt-2 text-sm text-error">{error}</span> : null}
    </label>
  );
}
