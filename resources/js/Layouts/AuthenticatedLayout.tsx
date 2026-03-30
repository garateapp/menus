import AppLayout from '@/Layouts/AppLayout';
import { PropsWithChildren, ReactNode } from 'react';

export default function AuthenticatedLayout({
  header,
  children,
}: PropsWithChildren<{ header?: ReactNode }>) {
  return (
    <AppLayout>
      {header ? <div className="mb-6">{header}</div> : null}
      {children}
    </AppLayout>
  );
}
