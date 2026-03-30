import { cleanup, render, screen } from '@testing-library/react';
import '@testing-library/jest-dom/vitest';
import WorkerDashboard from '@/Pages/Worker/Dashboard';
import type { ReactNode } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/react', () => ({
  Link: ({ children, href }: { children: ReactNode; href: string }) => <a href={href}>{children}</a>,
}));

vi.mock('@/Layouts/AppLayout', () => ({
  default: ({ children }: { children: ReactNode }) => <div>{children}</div>,
}));

vi.mock('@/Components/PageHeader', () => ({
  default: ({ title, description }: { title: string; description: string }) => (
    <div>
      <h1>{title}</h1>
      <p>{description}</p>
    </div>
  ),
}));

vi.mock('@/Components/StatCard', () => ({
  default: ({ label, value }: { label: string; value: string | number }) => (
    <div>
      <span>{label}</span>
      <span>{value}</span>
    </div>
  ),
}));

describe('WorkerDashboard', () => {
  beforeEach(() => {
    vi.stubGlobal('route', vi.fn(() => '/worker/menus/1'));
  });

  afterEach(() => {
    cleanup();
  });

  it('shows the number of alternatives when the menu uses the camelCase relation key', () => {
    render(
      <WorkerDashboard
        todayMenu={null}
        upcomingMenus={[
          {
            id: 1,
            weekly_menu_id: 1,
            menu_date: '2026-03-27',
            status: 'published',
            menuOptions: [
              {
                id: 10,
                daily_menu_id: 1,
                title: 'Pollo',
                description: null,
                image_path: null,
                quota: null,
                is_visible: true,
                sort_order: 1,
              },
              {
                id: 11,
                daily_menu_id: 1,
                title: 'Pasta',
                description: null,
                image_path: null,
                quota: null,
                is_visible: true,
                sort_order: 2,
              },
            ],
          },
        ]}
        recentSelections={[]}
      />,
    );

    expect(screen.getByText('2 alternativas')).toBeInTheDocument();
  });
});
