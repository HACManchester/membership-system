import { useRef, useState } from 'react';
import { Member } from '../types/resources';

type Options = {
  /** Member ids to drop from the results (e.g. members already trained). */
  exclude?: number[];
};

/**
 * Debounced on-demand member search against the members/search endpoint, so
 * pickers don't need the whole member list shipped up front. Returns the current
 * result set, a loading flag, and a `search(query)` handler to wire to an
 * Autocomplete's onInputChange.
 */
export function useMemberSearch(searchUrl: string, { exclude = [] }: Options = {}) {
  const [members, setMembers] = useState<Member[]>([]);
  const [searching, setSearching] = useState(false);
  const debounceRef = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

  const search = (query: string) => {
    if (debounceRef.current) clearTimeout(debounceRef.current);
    const excluded = new Set(exclude);
    debounceRef.current = setTimeout(async () => {
      setSearching(true);
      try {
        const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
          headers: { Accept: 'application/json' },
        });
        const results: Member[] = await response.json();
        setMembers(results.filter((member) => !excluded.has(member.id)));
      } finally {
        setSearching(false);
      }
    }, 300);
  };

  return { members, searching, search };
}
