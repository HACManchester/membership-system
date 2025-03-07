import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import PaymentModule from '../PaymentModule';
import $ from 'jquery';

// Mock jquery ajax
jest.mock('jquery', () => ({
  ajax: jest.fn()
}));

// Mock BB.SnackBar
global.BB = {
  SnackBar: {
    displayMessage: jest.fn()
  }
};

// Mock window.location
const location = {
  href: ''
};

Object.defineProperty(window, 'location', {
  value: location,
  writable: true
});

describe('PaymentModule', () => {
  const defaultProps = {
    userId: '123',
    amount: null,
    csrfToken: 'test-token',
    methods: 'gocardless',
    buttonLabel: 'Pay Now',
    reason: 'test-payment',
    reference: 'test-ref',
    onSuccess: jest.fn()
  };

  beforeEach(() => {
    jest.clearAllMocks();
    window.location.href = '';
  });

  it('renders with default props', () => {
    render(<PaymentModule {...defaultProps} />);
    
    expect(screen.getByRole('button')).toHaveTextContent('Pay Now');
    expect(screen.getByRole('spinbutton')).toBeInTheDocument();
    expect(screen.getByRole('combobox')).toHaveValue('gocardless');
  });

  it('shows fixed amount when amount prop is provided', () => {
    render(<PaymentModule {...defaultProps} amount="50.00" />);
    
    expect(screen.queryByRole('spinbutton')).not.toBeInTheDocument();
  });

  it('handles amount changes', () => {
    render(<PaymentModule {...defaultProps} />);
    
    const amountInput = screen.getByRole('spinbutton');
    fireEvent.change(amountInput, { target: { value: '25.50' } });
    
    expect(amountInput.value).toBe('25.50');
  });

  it('prevents negative amounts', async () => {
    render(<PaymentModule {...defaultProps} />);
    
    const amountInput = screen.getByRole('spinbutton');
    const submitButton = screen.getByRole('button');
    
    fireEvent.change(amountInput, { target: { value: '-10.00' } });
    fireEvent.click(submitButton);
    
    expect(await screen.findByText('Amount cannot be negative')).toBeInTheDocument();
  });

  it('validates invalid amount formats', async () => {
    render(<PaymentModule {...defaultProps} />);
    
    const amountInput = screen.getByRole('spinbutton');
    const submitButton = screen.getByRole('button');
    
    fireEvent.change(amountInput, { target: { value: 'abc' } });
    fireEvent.click(submitButton);
    
    expect(await screen.findByText('Invalid amount. Please re-enter')).toBeInTheDocument();
  });

  it('handles successful payment submission', async () => {
    $.ajax.mockImplementation(({ success }) => {
      success({});
    });

    render(<PaymentModule {...defaultProps} />);
    
    const amountInput = screen.getByRole('spinbutton');
    const submitButton = screen.getByRole('button');
    
    fireEvent.change(amountInput, { target: { value: '25.00' } });
    fireEvent.click(submitButton);

    await waitFor(() => {
      expect($.ajax).toHaveBeenCalledWith(expect.objectContaining({
        url: '/account/123/payment/gocardless',
        data: JSON.stringify({
          amount: '2500',
          reason: 'test-payment',
          ref: 'test-ref',
          _token: 'test-token'
        })
      }));
    });

    expect(BB.SnackBar.displayMessage).toHaveBeenCalledWith('Your payment has been processed');
    expect(defaultProps.onSuccess).toHaveBeenCalled();
  });

  it('handles payment submission errors', async () => {
    const errorResponse = {
      responseText: JSON.stringify({ error: 'Payment failed' })
    };

    $.ajax.mockImplementation(({ error }) => {
      error({ ...errorResponse, status: 400 });
    });

    render(<PaymentModule {...defaultProps} />);
    
    const amountInput = screen.getByRole('spinbutton');
    const submitButton = screen.getByRole('button');
    
    fireEvent.change(amountInput, { target: { value: '25.00' } });
    fireEvent.click(submitButton);

    expect(await screen.findByText('Payment failed')).toBeInTheDocument();
  });

  it('handles redirect responses', async () => {
    const redirectResponse = {
      responseText: JSON.stringify({ url: 'https://redirect.url' })
    };

    $.ajax.mockImplementation(({ error }) => {
      error({ ...redirectResponse, status: 303 });
    });

    render(<PaymentModule {...defaultProps} />);
    
    const amountInput = screen.getByRole('spinbutton');
    const submitButton = screen.getByRole('button');
    
    fireEvent.change(amountInput, { target: { value: '25.00' } });
    fireEvent.click(submitButton);

    await waitFor(() => {
      expect(window.location.href).toBe('https://redirect.url');
    });
  });
});