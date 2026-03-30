export function formatDisplayDate(value?: string | null): string {
  if (!value) {
    return '-';
  }

  const isoLikeDate = value.match(/^(\d{4})-(\d{2})-(\d{2})/);

  if (isoLikeDate) {
    const [, year, month, day] = isoLikeDate;

    return `${day}-${month}-${year}`;
  }

  const parsed = new Date(value);

  if (Number.isNaN(parsed.getTime())) {
    return value;
  }

  const day = `${parsed.getDate()}`.padStart(2, '0');
  const month = `${parsed.getMonth() + 1}`.padStart(2, '0');
  const year = parsed.getFullYear();

  return `${day}-${month}-${year}`;
}

export function formatDateInputValue(value?: string | null): string {
  if (!value) {
    return '';
  }

  const isoLikeDate = value.match(/^(\d{4})-(\d{2})-(\d{2})/);

  if (isoLikeDate) {
    return `${isoLikeDate[1]}-${isoLikeDate[2]}-${isoLikeDate[3]}`;
  }

  const parsed = new Date(value);

  if (Number.isNaN(parsed.getTime())) {
    return '';
  }

  const year = parsed.getFullYear();
  const month = `${parsed.getMonth() + 1}`.padStart(2, '0');
  const day = `${parsed.getDate()}`.padStart(2, '0');

  return `${year}-${month}-${day}`;
}
