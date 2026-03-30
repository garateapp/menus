import { ButtonHTMLAttributes } from 'react';

export default function SecondaryButton({
  type = 'button',
  className = '',
  disabled,
  children,
  ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
  return (
    <button
      {...props}
      type={type}
      className={`btn btn-outline rounded-full px-5 ${disabled ? 'btn-disabled opacity-60' : ''} ${className}`}
      disabled={disabled}
    >
      {children}
    </button>
  );
}
