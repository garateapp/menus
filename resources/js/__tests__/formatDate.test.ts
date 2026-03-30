import { describe, expect, it } from 'vitest';
import { formatDisplayDate } from '@/lib/format-date';

describe('formatDisplayDate', () => {
  it('formats plain yyyy-mm-dd values to dd-mm-yyyy', () => {
    expect(formatDisplayDate('2026-03-26')).toBe('26-03-2026');
  });

  it('formats datetime strings using the same dd-mm-yyyy visual format', () => {
    expect(formatDisplayDate('2026-03-26T15:30:00')).toBe('26-03-2026');
  });

  it('returns a dash for empty values', () => {
    expect(formatDisplayDate('')).toBe('-');
  });
});
