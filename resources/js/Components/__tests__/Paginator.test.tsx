import { describe, expect, it, jest } from '@jest/globals';
import { render, screen } from '@testing-library/react';
import { usePage } from '@inertiajs/react';
import Paginator from '../Paginator';
import { LengthAwarePaginated, SimplePaginated } from '../../types/pagination';

function onPage(url: string) {
  jest.mocked(usePage).mockReturnValue({ url, props: {} } as unknown as ReturnType<typeof usePage>);
}

function simple(current: number, hasNext: boolean): SimplePaginated<never> {
  return {
    data: [],
    current_page: current,
    per_page: 10,
    from: null,
    to: null,
    path: '/payments',
    first_page_url: '/payments?page=1',
    prev_page_url: current > 1 ? `/payments?page=${current - 1}` : null,
    next_page_url: hasNext ? `/payments?page=${current + 1}` : null,
  };
}

function lengthAware(current: number, last: number): LengthAwarePaginated<never> {
  return {
    ...simple(current, current < last),
    last_page: last,
    last_page_url: '',
    total: last * 10,
    links: [],
  };
}

describe('Paginator', () => {
  it('links every page, keeping the sort and filters', () => {
    onPage('/payments?sortBy=amount&direction=desc&page=2');
    render(<Paginator paginator={lengthAware(2, 3)} />);

    expect(screen.getByRole('link', { name: 'Go to page 3' })).toHaveAttribute(
      'href',
      '/payments?sortBy=amount&direction=desc&page=3'
    );
    expect(screen.getByRole('link', { name: 'Go to previous page' })).toHaveAttribute(
      'href',
      '/payments?sortBy=amount&direction=desc&page=1'
    );
    expect(screen.getByRole('link', { name: 'page 2' })).toHaveAttribute('aria-current', 'page');
  });

  it('uses the given page parameter', () => {
    onPage('/account?showLeft=1');
    render(<Paginator paginator={lengthAware(1, 2)} pageName="membersPage" />);

    expect(screen.getByRole('link', { name: 'Go to page 2' })).toHaveAttribute(
      'href',
      '/account?showLeft=1&membersPage=2'
    );
  });

  it('shows nothing for a single page', () => {
    onPage('/payments');
    const { container } = render(<Paginator paginator={lengthAware(1, 1)} />);

    expect(container).toBeEmptyDOMElement();
  });

  it('links the previous and next pages of a simple paginator', () => {
    onPage('/account/1/balance?page=2');
    render(<Paginator paginator={simple(2, true)} />);

    expect(screen.getByRole('link', { name: 'Previous' })).toHaveAttribute(
      'href',
      '/account/1/balance?page=1'
    );
    expect(screen.getByRole('link', { name: 'Next' })).toHaveAttribute(
      'href',
      '/account/1/balance?page=3'
    );
  });

  it('disables next on the last page of a simple paginator', () => {
    onPage('/account/1/balance?page=2');
    render(<Paginator paginator={simple(2, false)} />);

    expect(screen.getByRole('link', { name: 'Previous' })).toBeInTheDocument();
    expect(screen.queryByRole('link', { name: 'Next' })).not.toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Next' })).toBeDisabled();
  });

  it('shows nothing when a simple paginator has one page', () => {
    onPage('/account/1/balance');
    const { container } = render(<Paginator paginator={simple(1, false)} />);

    expect(container).toBeEmptyDOMElement();
  });
});
