import { ButtonHTMLAttributes } from 'react';

export default function DangerButton({
  className = '',
  disabled,
  children,
  ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      {...props}
      className={`btn btn-error rounded-full px-5 ${disabled ? 'btn-disabled opacity-60' : ''} ${className}`}
      disabled={disabled}
    >
      {children}
    </button>
  );
}
