import { usePage } from '@inertiajs/react';
import { TableCell, TableCellProps, TableSortLabel } from '@mui/material';
import { urlWithQuery } from '../utils/urlWithQuery';
import RouterLink from './RouterLink';

type SortParams = {
  sortParam?: string;
  directionParam?: string;
  /** The paginator's page parameter, dropped so a new sort starts on the first page. */
  pageParam?: string;
};

type SortLinkProps = SortParams & {
  /** The value of the sort parameter for this column. */
  column: string;
  children: React.ReactNode;
};

function useSort(
  column: string,
  { sortParam = 'sortBy', directionParam = 'direction', pageParam = 'page' }: SortParams
) {
  const { url } = usePage();
  const query = new URL(url, 'http://localhost').searchParams;
  const active = query.get(sortParam) === column;
  const direction: 'asc' | 'desc' = query.get(directionParam) === 'desc' ? 'desc' : 'asc';
  const nextDirection = active && direction === 'asc' ? 'desc' : 'asc';

  return {
    active,
    direction,
    // Back to the first page, keeping the filters.
    href: urlWithQuery(url, {
      [sortParam]: column,
      [directionParam]: nextDirection,
      [pageParam]: null,
    }),
  };
}

/**
 * A link that sorts the list by a column through the query string
 * (`?sortBy=column&direction=asc`), which the controller reads. Clicking the
 * sorted column again reverses it. Use it directly when one header cell holds
 * two sort links; otherwise use SortableHeader.
 */
export const SortLink = ({ column, children, ...params }: SortLinkProps) => {
  const { active, direction, href } = useSort(column, params);

  return (
    <TableSortLabel component={RouterLink} href={href} active={active} direction={direction}>
      {children}
    </TableSortLabel>
  );
};

type SortableHeaderProps = SortLinkProps & Pick<TableCellProps, 'align' | 'sx'>;

/**
 * A table header cell that sorts by its column: the Inertia counterpart of the
 * `sort-by` Blade partials.
 */
const SortableHeader = ({ column, children, align, sx, ...params }: SortableHeaderProps) => {
  const { active, direction } = useSort(column, params);

  return (
    <TableCell sortDirection={active ? direction : false} align={align} sx={sx}>
      <SortLink column={column} {...params}>
        {children}
      </SortLink>
    </TableCell>
  );
};

export default SortableHeader;
