/**
 * Format a past date relative to now ("today", "yesterday", "2 weeks ago"),
 * falling back to an absolute date beyond a year. Wording and localisation
 * are delegated to the browser's Intl.RelativeTimeFormat.
 */
export function formatDateRelative(dateString: string): string {
  const date = new Date(dateString);
  const diffDays = Math.floor((Date.now() - date.getTime()) / (1000 * 60 * 60 * 24));

  if (diffDays >= 365) {
    return date.toLocaleDateString();
  }

  const rtf = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });
  if (diffDays < 7) {
    return rtf.format(-diffDays, 'day');
  }
  if (diffDays < 30) {
    return rtf.format(-Math.floor(diffDays / 7), 'week');
  }
  return rtf.format(-Math.floor(diffDays / 30), 'month');
}
