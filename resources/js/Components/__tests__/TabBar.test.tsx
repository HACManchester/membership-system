import { describe, expect, it } from '@jest/globals';
import { render, screen, within } from '@testing-library/react';
import TabBar from '../TabBar';

const tabs = [
  { label: 'All Payments', href: '/payments', active: true },
  { label: 'Overview', href: '/payments/overview' },
];

describe('TabBar', () => {
  it('links each page in a nav and marks the current one', () => {
    render(<TabBar tabs={tabs} aria-label="Payments" />);

    const nav = screen.getByRole('navigation', { name: 'Payments' });
    expect(within(nav).getByRole('link', { name: 'All Payments' })).toHaveAttribute(
      'href',
      '/payments'
    );
    expect(within(nav).getByRole('link', { name: 'All Payments' })).toHaveAttribute(
      'aria-current',
      'page'
    );
    expect(within(nav).getByRole('link', { name: 'Overview' })).toHaveAttribute(
      'href',
      '/payments/overview'
    );
    expect(within(nav).getByRole('link', { name: 'Overview' })).not.toHaveAttribute('aria-current');
  });

  it('uses plain links, not ARIA tabs', () => {
    render(<TabBar tabs={tabs} aria-label="Payments" />);

    expect(screen.queryByRole('tablist')).not.toBeInTheDocument();
    expect(screen.queryByRole('tab')).not.toBeInTheDocument();
    screen.getAllByRole('link').forEach((link) => expect(link).not.toHaveAttribute('tabindex'));
  });
});
