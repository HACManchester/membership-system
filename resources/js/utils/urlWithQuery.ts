/**
 * The page's URL (Inertia's relative `usePage().url`) with some query parameters
 * changed: a string sets one, null removes it, and the rest are kept, so a
 * sort or page link doesn't lose the list's filters.
 */
export function urlWithQuery(url: string, changes: Record<string, string | null>): string {
  const parsed = new URL(url, 'http://localhost');

  Object.entries(changes).forEach(([name, value]) => {
    if (value === null) {
      parsed.searchParams.delete(name);
    } else {
      parsed.searchParams.set(name, value);
    }
  });

  return parsed.pathname + parsed.search;
}
