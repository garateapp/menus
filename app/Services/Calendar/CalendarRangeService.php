<?php

namespace App\Services\Calendar;

use Carbon\CarbonImmutable;

class CalendarRangeService
{
    private const LOCALE = 'es';

    public function build(?string $view, ?string $date): array
    {
        $resolvedView = $this->resolveView($view);
        $focusDate = $this->resolveDate($date);

        return match ($resolvedView) {
            'month' => $this->buildMonthRange($focusDate),
            'day' => $this->buildDayRange($focusDate),
            default => $this->buildWeekRange($focusDate),
        };
    }

    public function weekBounds(CarbonImmutable $date): array
    {
        return [
            'week_start_date' => $date->startOfWeek()->toDateString(),
            'week_end_date' => $date->endOfWeek()->toDateString(),
        ];
    }

    private function buildMonthRange(CarbonImmutable $focusDate): array
    {
        $monthStart = $focusDate->startOfMonth();
        $monthEnd = $focusDate->endOfMonth();
        $visibleStart = $monthStart->startOfWeek();
        $visibleEnd = $monthEnd->endOfWeek();

        return [
            'view' => 'month',
            'focus_date' => $focusDate->toDateString(),
            'label' => $focusDate->locale(self::LOCALE)->translatedFormat('F Y'),
            'range_start' => $visibleStart->toDateString(),
            'range_end' => $visibleEnd->toDateString(),
            'previous_date' => $focusDate->subMonth()->toDateString(),
            'next_date' => $focusDate->addMonth()->toDateString(),
            'days' => $this->makeDayEntries($visibleStart, $visibleEnd, $focusDate),
        ];
    }

    private function buildWeekRange(CarbonImmutable $focusDate): array
    {
        $visibleStart = $focusDate->startOfWeek();
        $visibleEnd = $focusDate->endOfWeek();

        return [
            'view' => 'week',
            'focus_date' => $focusDate->toDateString(),
            'label' => sprintf('%s al %s', $visibleStart->format('d-m-Y'), $visibleEnd->format('d-m-Y')),
            'range_start' => $visibleStart->toDateString(),
            'range_end' => $visibleEnd->toDateString(),
            'previous_date' => $focusDate->subWeek()->toDateString(),
            'next_date' => $focusDate->addWeek()->toDateString(),
            'days' => $this->makeDayEntries($visibleStart, $visibleEnd, $focusDate),
        ];
    }

    private function buildDayRange(CarbonImmutable $focusDate): array
    {
        return [
            'view' => 'day',
            'focus_date' => $focusDate->toDateString(),
            'label' => $focusDate->format('d-m-Y'),
            'range_start' => $focusDate->toDateString(),
            'range_end' => $focusDate->toDateString(),
            'previous_date' => $focusDate->subDay()->toDateString(),
            'next_date' => $focusDate->addDay()->toDateString(),
            'days' => $this->makeDayEntries($focusDate, $focusDate, $focusDate),
        ];
    }

    private function makeDayEntries(CarbonImmutable $start, CarbonImmutable $end, CarbonImmutable $focusDate): array
    {
        $days = [];
        $current = $start;

        while ($current->lte($end)) {
            $days[] = [
                'date' => $current->toDateString(),
                'day_number' => (int) $current->format('d'),
                'day_name' => $current->locale(self::LOCALE)->translatedFormat('D'),
                'is_today' => $current->isSameDay(today()),
                'is_focus_date' => $current->isSameDay($focusDate),
                'is_current_month' => $current->month === $focusDate->month,
            ];

            $current = $current->addDay();
        }

        return $days;
    }

    private function resolveView(?string $view): string
    {
        return in_array($view, ['month', 'week', 'day'], true) ? $view : 'week';
    }

    private function resolveDate(?string $date): CarbonImmutable
    {
        if (! $date) {
            return CarbonImmutable::today();
        }

        try {
            return CarbonImmutable::parse($date)->startOfDay();
        } catch (\Throwable) {
            return CarbonImmutable::today();
        }
    }
}
