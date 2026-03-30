import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom/vitest';
import Welcome from '@/Pages/Welcome';
import type { ReactNode } from 'react';
import { describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/react', () => ({
  Head: () => null,
  Link: ({ children, href, className }: { children: ReactNode; href: string; className?: string }) => (
    <a href={href} className={className}>
      {children}
    </a>
  ),
}));

describe('Welcome', () => {
  it('does not require the register route when registration is disabled', () => {
    vi.stubGlobal(
      'route',
      vi.fn((name: string) => {
        if (name === 'register') {
          throw new Error("Ziggy error: route 'register' is not in the route list.");
        }

        return `/${name}`;
      }),
    );

    expect(() =>
      render(
        <Welcome
          auth={{ user: null }}
          flash={{}}
          laravelVersion="12.0.0"
          phpVersion="8.3.0"
          canLogin
          canRegister={false}
        />,
      ),
    ).not.toThrow();

    expect(screen.queryByText('Register')).not.toBeInTheDocument();
  });
});
