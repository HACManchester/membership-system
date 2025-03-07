import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import Select from '../Select';

describe('Select', () => {
  const defaultProps = {
    options: [
      { key: 'option1', value: 'Option 1' },
      { key: 'option2', value: 'Option 2' }
    ],
    value: 'option1',
    onChange: jest.fn()
  };

  it('renders with default props', () => {
    render(<Select {...defaultProps} />);
    
    expect(screen.getByRole('combobox')).toHaveValue('option1');
    expect(screen.getAllByRole('option')).toHaveLength(2);
  });

  it('displays label when provided', () => {
    render(<Select {...defaultProps} label="Test Label" />);
    
    expect(screen.getByText('Test Label')).toBeInTheDocument();
  });

  it('displays help text when provided', () => {
    render(<Select {...defaultProps} help="Help message" />);
    
    expect(screen.getByText('Help message')).toBeInTheDocument();
  });

  it('applies bootstrap style class when provided', () => {
    render(<Select {...defaultProps} bsStyle="error" />);
    
    expect(screen.getByRole('combobox').parentElement).toHaveClass('has-error');
  });

  it('calls onChange handler when selection changes', () => {
    render(<Select {...defaultProps} />);
    
    fireEvent.change(screen.getByRole('combobox'), { target: { value: 'option2' } });
    
    expect(defaultProps.onChange).toHaveBeenCalled();
  });
});