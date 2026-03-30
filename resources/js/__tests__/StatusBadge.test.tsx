import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom/vitest';
import StatusBadge from '@/Components/StatusBadge';
import { describe, expect, it } from 'vitest';

describe('StatusBadge', () => {
  it('renders the published label in spanish', () => {
    render(<StatusBadge status="published" />);

    expect(screen.getByText('Publicado')).toBeInTheDocument();
  });

  it('renders the closed label in spanish', () => {
    render(<StatusBadge status="closed" />);

    expect(screen.getByText('Cerrado')).toBeInTheDocument();
  });
});
