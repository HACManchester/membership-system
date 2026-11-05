import { describe, expect, it } from '@jest/globals';
import { render, screen } from '@testing-library/react';
import StatusChip, { MemberStatus } from '../StatusChip';
import SpaceAccessChip from '../SpaceAccessChip';

describe('StatusChip', () => {
  it.each([
    ['setting-up', 'Setting Up', 'We are waiting for a subscription to be setup'],
    ['payment-warning', 'Payment Warning', 'There is something wrong with your subscription'],
    ['leaving', 'Leaving', 'Your leaving and will loose access when your payment expires'],
    [
      'suspended',
      'Suspended',
      'Your payment has failed, please make a manual payment to reactivate your account.',
    ],
  ])('labels %s with an explanation', (status, label, description) => {
    render(<StatusChip status={status as MemberStatus} />);

    expect(screen.getByText(label)).toBeInTheDocument();
    expect(screen.getByLabelText(description)).toBeInTheDocument();
  });

  it.each([
    ['active', 'Active'],
    ['on-hold', 'On Hold'],
    ['left', 'Left'],
    ['honorary', 'Honorary'],
  ])('labels %s', (status, label) => {
    render(<StatusChip status={status as MemberStatus} />);

    expect(screen.getByText(label)).toBeInTheDocument();
  });

  it('shows nothing for an unknown status', () => {
    const { container } = render(<StatusChip status={'unknown' as MemberStatus} />);

    expect(container).toBeEmptyDOMElement();
  });
});

describe('SpaceAccessChip', () => {
  it('says when the member can use the space', () => {
    render(<SpaceAccessChip active />);

    expect(screen.getByLabelText('You are allowed to use the space')).toHaveTextContent(
      'Access to the space'
    );
  });

  it('says when the member cannot use the space', () => {
    render(<SpaceAccessChip active={false} />);

    expect(screen.getByLabelText('You do not have permission to use the space')).toHaveTextContent(
      'No access to the space'
    );
  });
});
