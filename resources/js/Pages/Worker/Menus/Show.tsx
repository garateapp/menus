import MenuOptionCard from '@/Components/MenuOptionCard';
import PageHeader from '@/Components/PageHeader';
import AppLayout from '@/Layouts/AppLayout';
import { formatDisplayDate } from '@/lib/format-date';
import type { DailyMenu, MenuSelection } from '@/types';

type Props = {
  menu: DailyMenu;
  currentSelection?: MenuSelection | null;
};

export default function Show({ menu, currentSelection }: Props) {
  const options = menu.menu_options ?? menu.menuOptions ?? [];

  return (
    <AppLayout>
      <PageHeader title={`Menú del ${formatDisplayDate(menu.menu_date)}`} description="Escoge una única alternativa para este día." />

      <div className="grid gap-6 lg:grid-cols-2">
        {options.map((option) => (
          <MenuOptionCard
            key={option.id}
            dailyMenuId={menu.id}
            option={option}
            selected={currentSelection?.menu_option_id === option.id}
            disabled={menu.status === 'closed'}
          />
        ))}
      </div>
    </AppLayout>
  );
}
