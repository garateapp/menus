import { ButtonHTMLAttributes } from 'react';

export default function PrimaryButton({
  className = '',
  disabled,
  children,
  ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      {...props}
      className={`btn btn-primary rounded-full px-5 ${disabled ? 'btn-disabled opacity-60' : ''} ${className}`}
      disabled={disabled}
    >
      {children}
    </button>
  );
}
