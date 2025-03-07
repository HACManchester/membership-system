import React from 'react';
import { render, screen } from '@testing-library/react';
import PaymentTableRow from '../PaymentTableRow';

describe('PaymentTableRow', () => {
  const mockPayment = {
    date: '2023-01-01',
    user: { name: 'John Doe' },
    reason: 'Membership',
    method: 'Direct Debit',
    amount: '£25.00',
    reference: 'REF123',
    status: 'Paid'
  };

  it('renders payment details in table row', () => {
    render(<table><tbody><PaymentTableRow payment={mockPayment} /></tbody></table>);
    
    expect(screen.getByText(mockPayment.date)).toBeInTheDocument();
    expect(screen.getByText(mockPayment.user.name)).toBeInTheDocument();
    expect(screen.getByText(mockPayment.reason)).toBeInTheDocument();
    expect(screen.getByText(mockPayment.method)).toBeInTheDocument();
    expect(screen.getByText(mockPayment.amount)).toBeInTheDocument();
    expect(screen.getByText(mockPayment.reference)).toBeInTheDocument();
    expect(screen.getByText(mockPayment.status)).toBeInTheDocument();
  });

  it('renders empty table cell at the end', () => {
    render(<table><tbody><PaymentTableRow payment={mockPayment} /></tbody></table>);
    
    const cells = screen.getAllByRole('cell');
    expect(cells).toHaveLength(8); // 7 data cells + 1 empty cell
    expect(cells[cells.length - 1]).toHaveTextContent('');
  });
});