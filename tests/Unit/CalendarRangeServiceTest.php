<?php

namespace Tests\Unit;

use App\Services\Calendar\CalendarRangeService;
use PHPUnit\Framework\TestCase;

class CalendarRangeServiceTest extends TestCase
{
    public function test_it_builds_a_month_range_with_a_full_grid(): void
    {
        $service = new CalendarRangeService();

        $range = $service->build('month', '2026-03-30');

        $this->assertSame('month', $range['view']);
        $this->assertSame('2026-02-23', $range['range_start']);
        $this->assertSame('2026-04-05', $range['range_end']);
        $this->assertCount(42, $range['days']);
        $this->assertSame('marzo 2026', mb_strtolower($range['label']));
        $this->assertSame('lun.', mb_strtolower($range['days'][0]['day_name']));
    }

    public function test_it_builds_a_week_range_with_seven_days(): void
    {
        $service = new CalendarRangeService();

        $range = $service->build('week', '2026-03-30');

        $this->assertSame('week', $range['view']);
        $this->assertSame('2026-03-30', $range['range_start']);
        $this->assertSame('2026-04-05', $range['range_end']);
        $this->assertCount(7, $range['days']);
    }

    public function test_it_builds_a_day_range_with_a_single_day(): void
    {
        $service = new CalendarRangeService();

        $range = $service->build('day', '2026-03-30');

        $this->assertSame('day', $range['view']);
        $this->assertSame('2026-03-30', $range['range_start']);
        $this->assertSame('2026-03-30', $range['range_end']);
        $this->assertCount(1, $range['days']);
    }
}
