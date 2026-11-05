// Laravel's paginators as JSON. Send them with `->through(fn ($item) =>
// (new SomeResource($item))->resolve())` so `data` holds only what the page shows.

/** `simplePaginate()`: no total, so only previous and next. */
export type SimplePaginated<T> = {
  data: T[];
  current_page: number;
  per_page: number;
  from: number | null;
  to: number | null;
  path: string;
  first_page_url: string;
  prev_page_url: string | null;
  next_page_url: string | null;
};

/** `paginate()`: knows the total, so every page can be linked. */
export type LengthAwarePaginated<T> = SimplePaginated<T> & {
  last_page: number;
  last_page_url: string;
  total: number;
  links: { url: string | null; label: string; active: boolean }[];
};

export type Paginated<T> = SimplePaginated<T> | LengthAwarePaginated<T>;
