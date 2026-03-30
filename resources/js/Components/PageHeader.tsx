import { Link } from '@inertiajs/react';
import { ReactNode } from 'react';

type Props = {
  title: string;
  description?: string;
  action?: {
    label: string;
    href: string;
  };
  extra?: ReactNode;
};

export default function PageHeader({ title, description, action, extra }: Props) {
  return (
    <div className="panel-card mb-6 overflow-hidden">
      <div className="flex flex-col gap-5 px-6 py-6 sm:px-8 lg:flex-row lg:items-end lg:justify-between">
        <div className="max-w-3xl">
          <p className="section-kicker">Workspace</p>
          <h1 className="mt-3 font-display text-3xl font-semibold tracking-tight text-base-content sm:text-4xl">{title}</h1>
          {description ? <p className="mt-3 max-w-2xl text-sm leading-6 text-base-content/62 sm:text-base">{description}</p> : null}
        </div>

        <div className="flex flex-wrap items-center gap-3">
          {extra}
          {action ? (
            <Link href={action.href} className="btn btn-primary rounded-full px-5">
              {action.label}
            </Link>
          ) : null}
        </div>
      </div>
    </div>
  );
}
