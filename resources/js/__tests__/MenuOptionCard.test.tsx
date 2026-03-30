import { cleanup, render, screen } from '@testing-library/react';
import '@testing-library/jest-dom/vitest';
import MenuOptionCard from '@/Components/MenuOptionCard';
import type { ReactNode } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const postMock = vi.fn();

vi.mock('@inertiajs/react', () => ({
  Link: ({ children }: { children: ReactNode }) => children,
  useForm: () => ({
    data: {},
    processing: false,
    post: postMock,
  }),
}));

describe('MenuOptionCard', () => {
  beforeEach(() => {
    postMock.mockReset();
    vi.stubGlobal('route', vi.fn(() => '/worker/daily-menus/10/selections'));
  });

  afterEach(() => {
    cleanup();
  });

  it('renders the option details and selected state', () => {
    render(
      <MenuOptionCard
        dailyMenuId={10}
        selected
        option={{
          id: 3,
          daily_menu_id: 10,
          title: 'Pollo al horno',
          description: 'Con arroz y ensalada',
          image_path: null,
          quota: null,
          is_visible: true,
          sort_order: 1,
          totalSelections: 0,
        }}
      />,
    );

    expect(screen.getByText('Pollo al horno')).toBeInTheDocument();
    expect(screen.getByText('Con arroz y ensalada')).toBeInTheDocument();
    expect(screen.getByText('Tu elección')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Cambiar selección' })).toBeEnabled();
  });

  it('disables the action button when the card is disabled', () => {
    render(
      <MenuOptionCard
        dailyMenuId={10}
        selected={false}
        disabled
        option={{
          id: 4,
          daily_menu_id: 10,
          title: 'Pasta primavera',
          description: null,
          image_path: null,
          quota: 30,
          is_visible: true,
          sort_order: 2,
          totalSelections: 0,
        }}
      />,
    );

    expect(screen.getByRole('button', { name: 'Elegir opción' })).toBeDisabled();
  });

  it('shows remaining quota and disables sold out options that are not selected', () => {
    render(
      <MenuOptionCard
        dailyMenuId={10}
        selected={false}
        option={{
          id: 5,
          daily_menu_id: 10,
          title: 'Pastel de choclo',
          description: null,
          image_path: null,
          quota: 10,
          remaining_quota: 0,
          is_sold_out: true,
          is_visible: true,
          sort_order: 3,
          totalSelections: 10,
        }}
      />,
    );

    expect(screen.getByText('Cupos disponibles: 0 de 10')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Sin cupos' })).toBeDisabled();
  });

  it('keeps the current selection actionable even when remaining quota is zero', () => {
    render(
      <MenuOptionCard
        dailyMenuId={10}
        selected
        option={{
          id: 6,
          daily_menu_id: 10,
          title: 'Lentejas',
          description: null,
          image_path: null,
          quota: 1,
          remaining_quota: 0,
          is_sold_out: true,
          is_visible: true,
          sort_order: 4,
          totalSelections: 1,
        }}
      />,
    );

    expect(screen.getByText('Cupos disponibles: 0 de 1')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Cambiar selección' })).toBeEnabled();
  });
});
