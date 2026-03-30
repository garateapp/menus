type Props = {
  previewUrl?: string | null;
};

export default function ImageUploadPreview({ previewUrl }: Props) {
  if (!previewUrl) {
    return (
      <div className="flex h-36 items-center justify-center rounded-2xl border border-dashed border-base-300 bg-base-200 text-sm text-base-content/50">
        Sin imagen
      </div>
    );
  }

  return <img src={previewUrl} alt="Preview" className="h-36 w-full rounded-2xl object-cover" />;
}
