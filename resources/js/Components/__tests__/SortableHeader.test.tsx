import { describe, expect, it, jest } from '@jest/globals';
import { render, screen } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import { Table, TableHead, TableRow } from '@mui/material';
import SortableHeader, { SortLink } from '../SortableHeader';

function onPage(url: string) {
  jest.mocked(usePage).mockReturnValue({ url, props: {} } as unknown as ReturnType<typeof usePage>);
}

function renderHeader(header: React.ReactNode) {
  return render(
    <Table>
      <TableHead>
        <TableRow>{header}</TableRow>
      </TableHead>
    </Table>
  );
}

describe('SortableHeader', () => {
  it('sorts by its column ascending, keeping filters and going back to the first page', () => {
    onPage('/payments?sortBy=amount&direction=desc&member_filter=4&page=3');
    renderHeader(<SortableHeader column="created_at">Date</SortableHeader>);

    expect(screen.getByRole('link', { name: 'Date' })).toHaveAttribute(
      'href',
      '/payments?sortBy=created_at&direction=asc&member_filter=4'
    );
    expect(screen.getByRole('columnheader')).not.toHaveAttribute('aria-sort');
  });

  it('reverses the column it is sorted by', () => {
    onPage('/payments?sortBy=amount&direction=asc');
    renderHeader(<SortableHeader column="amount">Amount</SortableHeader>);

    expect(screen.getByRole('link', { name: /Amount/ })).toHaveAttribute(
      'href',
      '/payments?sortBy=amount&direction=desc'
    );
    expect(screen.getByRole('columnheader')).toHaveAttribute('aria-sort', 'ascending');
  });

  it('sorts ascending again after descending', () => {
    onPage('/payments?sortBy=amount&direction=desc');
    renderHeader(<SortableHeader column="amount">Amount</SortableHeader>);

    expect(screen.getByRole('link', { name: /Amount/ })).toHaveAttribute(
      'href',
      '/payments?sortBy=amount&direction=asc'
    );
    expect(screen.getByRole('columnheader')).toHaveAttribute('aria-sort', 'descending');
  });

  it('drops the given page parameter', () => {
    onPage('/account?sortBy=name&membersPage=3');
    renderHeader(
      <SortableHeader column="status" pageParam="membersPage">
        Status
      </SortableHeader>
    );

    expect(screen.getByRole('link', { name: 'Status' })).toHaveAttribute(
      'href',
      '/account?sortBy=status&direction=asc'
    );
  });

  it('can hold two sort links in one cell', () => {
    onPage('/account?showLeft=1');
    render(
      <>
        <SortLink column="created_at">Created</SortLink>
        <SortLink column="seen_at">Seen</SortLink>
      </>
    );

    expect(screen.getByRole('link', { name: 'Created' })).toHaveAttribute(
      'href',
      '/account?showLeft=1&sortBy=created_at&direction=asc'
    );
    expect(screen.getByRole('link', { name: 'Seen' })).toHaveAttribute(
      'href',
      '/account?showLeft=1&sortBy=seen_at&direction=asc'
    );
  });
});
