import React from 'react';
import { render, screen } from '@testing-library/react';
import Tick from '../Tick';

describe('Tick', () => {
  it('renders tick when ticked is true', () => {
    render(<Tick ticked={true} title="Test Tick" />);
    
    const tick = screen.getByTitle('Test Tick');
    expect(tick).toBeInTheDocument();
    expect(tick).toHaveClass('glyphicon', 'glyphicon-ok');
  });

  it('returns null when ticked is false', () => {
    const { container } = render(<Tick ticked={false} title="Test Tick" />);
    
    expect(container.firstChild).toBeNull();
  });

  it('renders without title', () => {
    const { container } = render(<Tick ticked={true} />);
    
    const tick = container.querySelector('.glyphicon');
    expect(tick).toHaveClass('glyphicon', 'glyphicon-ok');
  });
});