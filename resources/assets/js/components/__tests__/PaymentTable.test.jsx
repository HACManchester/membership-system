import React from 'react';
import { render, screen, within } from '@testing-library/react';
import PaymentTable from '../PaymentTable';

describe('PaymentTable', () => {
  const mockPayments = [
    {
      id: 1,
      date: '2023-01-01',
      user: { name: 'John Doe' },
      reason: 'Membership',
      method: 'Direct Debit',
      amount: '£25.00',
      reference: 'REF123',
      status: 'Paid'
    },
    {
      id: 2,
      date: '2023-01-02',
      user: { name: 'Jane Smith' },
      reason: 'Door Key',
      method: 'Direct Debit',
      amount: '£10.00',
      reference: 'REF124',
      status: 'Pending'
    }
  ];

  it('renders payment table with headers', () => {
    render(<PaymentTable payments={mockPayments} />);
    
    expect(screen.getByRole('heading')).toHaveTextContent('New Payment List');
    expect(screen.getByRole('table')).toBeInTheDocument();
    
    // Check headers
    expect(screen.getByText('Date')).toBeInTheDocument();
    expect(screen.getByText('Member')).toBeInTheDocument();
    expect(screen.getByText('Reason')).toBeInTheDocument();
    expect(screen.getByText('Method')).toBeInTheDocument();
    expect(screen.getByText('Amount')).toBeInTheDocument();
    expect(screen.getByText('Reference')).toBeInTheDocument();
    expect(screen.getByText('Status')).toBeInTheDocument();
  });

  it('renders correct number of payment rows', () => {
    render(<PaymentTable payments={mockPayments} />);
    
    const rows = screen.getAllByRole('row');
    // +1 for header row
    expect(rows).toHaveLength(mockPayments.length + 1);
  });

  it('displays payment details in rows', () => {
    render(<PaymentTable payments={mockPayments} />);
    
    const rows = screen.getAllByRole('row').slice(1); // Skip header row
    
    mockPayments.forEach((payment, index) => {
      const row = rows[index];
      const cells = within(row).getAllByRole('cell');
      
      expect(cells[0]).toHaveTextContent(payment.date);
      expect(cells[1]).toHaveTextContent(payment.user.name);
      expect(cells[2]).toHaveTextContent(payment.reason);
      expect(cells[3]).toHaveTextContent(payment.method);
      expect(cells[4]).toHaveTextContent(payment.amount);
      expect(cells[5]).toHaveTextContent(payment.reference);
      expect(cells[6]).toHaveTextContent(payment.status);
    });
  });

  it('renders empty table with no payments', () => {
    render(<PaymentTable payments={[]} />);
    
    const rows = screen.getAllByRole('row');
    // Only header row should be present
    expect(rows).toHaveLength(1);
  });
});