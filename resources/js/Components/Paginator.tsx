import { usePage } from '@inertiajs/react';
import { Button, Pagination, PaginationItem, Stack } from '@mui/material';
import ChevronLeftIcon from '@mui/icons-material/ChevronLeft';
import ChevronRightIcon from '@mui/icons-material/ChevronRight';
import { Paginated } from '../types/pagination';
import { urlWithQuery } from '../utils/urlWithQuery';
import RouterLink from './RouterLink';

type Props = {
  paginator: Paginated<unknown>;
  /** The query parameter the paginator reads, if not `page`. */
  pageName?: string;
};

/**
 * Page links for a Laravel paginator: every page for `paginate()`, previous and
 * next for `simplePaginate()`. Links keep the page's other query parameters
 * (sort, filters), as the Blade `paginator-links` partials did with `appends()`.
 * Shows nothing when there's only one page.
 */
const Paginator = ({ paginator, pageName = 'page' }: Props) => {
  const { url } = usePage();
  const pageUrl = (page: number) => urlWithQuery(url, { [pageName]: String(page) });

  if ('last_page' in paginator) {
    if (paginator.last_page <= 1) {
      return null;
    }

    return (
      <Pagination
        page={paginator.current_page}
        count={paginator.last_page}
        renderItem={(item) => (
          <PaginationItem
            {...item}
            {...(item.page !== null && !item.disabled
              ? { component: RouterLink, href: pageUrl(item.page) }
              : {})}
          />
        )}
        sx={{ my: 2, '& .MuiPagination-ul': { justifyContent: 'center' } }}
      />
    );
  }

  const hasPrevious = paginator.prev_page_url !== null;
  const hasNext = paginator.next_page_url !== null;

  if (!hasPrevious && !hasNext) {
    return null;
  }

  return (
    <Stack
      component="nav"
      aria-label="pagination navigation"
      direction="row"
      spacing={2}
      sx={{ my: 2, justifyContent: 'center' }}
    >
      <Button
        startIcon={<ChevronLeftIcon />}
        disabled={!hasPrevious}
        {...(hasPrevious
          ? { component: RouterLink, href: pageUrl(paginator.current_page - 1) }
          : {})}
      >
        Previous
      </Button>
      <Button
        endIcon={<ChevronRightIcon />}
        disabled={!hasNext}
        {...(hasNext ? { component: RouterLink, href: pageUrl(paginator.current_page + 1) } : {})}
      >
        Next
      </Button>
    </Stack>
  );
};

export default Paginator;
