import type { PaginationLink } from '@/types';
import { Link } from '@inertiajs/react';

export default function Pagination({ links }: { links: PaginationLink[] }) {
  if (links.length <= 3) {
    return null;
  }

  return (
    <div className="join mt-6">
      {links.map((link, index) =>
        link.url ? (
          <Link
            key={`${link.label}-${index}`}
            href={link.url}
            className={`join-item btn btn-sm ${link.active ? 'btn-primary' : 'btn-outline'}`}
            dangerouslySetInnerHTML={{ __html: link.label }}
          />
        ) : (
          <button key={`${link.label}-${index}`} className="join-item btn btn-sm btn-disabled" dangerouslySetInnerHTML={{ __html: link.label }} />
        ),
      )}
    </div>
  );
}
