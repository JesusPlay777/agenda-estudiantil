<?php

namespace App\Http\Controllers\Dashboard\Concerns;

use App\Models\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait SortsSchedulesByWeekday
{
    /**
     * @param  Collection<int, Schedule>  $schedules
     * @return Collection<int, Schedule>
     */
    protected function sortSchedulesByWeekday(Collection $schedules): Collection
    {
        return $schedules
            ->sort(function (Schedule $left, Schedule $right): int {
                $weekdayComparison = $this->resolveWeekdaySortIndex($left->weekday)
                    <=> $this->resolveWeekdaySortIndex($right->weekday);

                if ($weekdayComparison !== 0) {
                    return $weekdayComparison;
                }

                return strcmp((string) $left->start_time, (string) $right->start_time);
            })
            ->values();
    }

    protected function resolveWeekdaySortIndex(?string $weekday): int
    {
        return match ($this->normalizeWeekday($weekday)) {
            'lunes' => 1,
            'martes' => 2,
            'miercoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
            default => 99,
        };
    }

    /**
     * @param  Collection<int, Schedule>  $schedules
     * @return Collection<int, array{weekday: string, schedules: Collection<int, Schedule>}>
     */
    protected function groupSchedulesByWeekday(Collection $schedules): Collection
    {
        $groupedSchedules = $this->sortSchedulesByWeekday($schedules)
            ->groupBy(fn (Schedule $schedule): string => $this->normalizeWeekday($schedule->weekday));

        return collect($this->orderedWeekdays())
            ->map(fn (string $weekday): array => [
                'weekday' => $weekday,
                'schedules' => $groupedSchedules->get($weekday, collect())->values(),
            ]);
    }

    /**
     * @param  Collection<int, Schedule>  $schedules
     * @return Collection<int, array{
     *     weekday: string,
     *     morning: Collection<int, Schedule>,
     *     afternoon: Collection<int, Schedule>
     * }>
     */
    protected function groupSchedulesByWeekdayAndShift(Collection $schedules): Collection
    {
        $groupedSchedules = $this->sortSchedulesByWeekday($schedules)
            ->groupBy(fn (Schedule $schedule): string => $this->normalizeWeekday($schedule->weekday));

        return collect($this->orderedWeekdays())
            ->map(function (string $weekday) use ($groupedSchedules): array {
                $weekdaySchedules = $groupedSchedules->get($weekday, collect())->values();

                return [
                    'weekday' => $weekday,
                    'morning' => $weekdaySchedules
                        ->filter(fn (Schedule $schedule): bool => $this->resolveScheduleShift($schedule) === 'morning')
                        ->values(),
                    'afternoon' => $weekdaySchedules
                        ->filter(fn (Schedule $schedule): bool => $this->resolveScheduleShift($schedule) === 'afternoon')
                        ->values(),
                ];
            });
    }

    /**
     * @return list<string>
     */
    protected function orderedWeekdays(): array
    {
        return ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
    }

    protected function normalizeWeekday(?string $weekday): string
    {
        return Str::of((string) $weekday)
            ->lower()
            ->ascii()
            ->replace(' ', '')
            ->toString();
    }

    protected function resolveScheduleShift(Schedule $schedule): string
    {
        $hour = (int) Str::before((string) $schedule->start_time, ':');

        return $hour < 12 ? 'morning' : 'afternoon';
    }
}
