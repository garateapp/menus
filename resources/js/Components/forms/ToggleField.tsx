type Props = {
  label: string;
  checked: boolean;
  onChange: (value: boolean) => void;
};

export default function ToggleField({ label, checked, onChange }: Props) {
  return (
    <label className="label cursor-pointer justify-start gap-3">
      <input
        type="checkbox"
        className="toggle toggle-primary"
        checked={checked}
        onChange={(event) => onChange(event.target.checked)}
      />
      <span className="label-text">{label}</span>
    </label>
  );
}
