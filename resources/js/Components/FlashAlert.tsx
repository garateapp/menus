type Props = {
  success?: string | null;
  error?: string | null;
};

function AlertRow({ tone, message }: { tone: 'success' | 'error'; message: string }) {
  const toneClasses = tone === 'success'
    ? 'border-success/25 bg-success/10 text-success-content'
    : 'border-error/25 bg-error/10 text-error-content';

  return (
    <div className={`soft-surface mb-4 border px-4 py-4 ${toneClasses}`}>
      <div className="flex items-start gap-3">
        <span className={`mt-1 h-2.5 w-2.5 rounded-full ${tone === 'success' ? 'bg-success' : 'bg-error'}`} />
        <p className="text-sm font-medium leading-6">{message}</p>
      </div>
    </div>
  );
}

export default function FlashAlert({ success, error }: Props) {
  if (!success && !error) {
    return null;
  }

  return (
    <div className="mb-6">
      {success ? <AlertRow tone="success" message={success} /> : null}
      {error ? <AlertRow tone="error" message={error} /> : null}
    </div>
  );
}
