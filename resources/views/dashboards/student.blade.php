<x-layouts::app :title="__('Student Dashboard')">
    <div class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <h1 class="text-2xl font-semibold">{{ __('Student Dashboard') }}</h1>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                {{ __('Review your section, subjects, weekly schedule, and assigned tasks.') }}
            </p>
        </div>

        @if (! $studentProfile)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-100">
                {{ __('Your student profile is not configured yet. Contact an administrator to assign your section.') }}
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold">{{ __('Current section') }}</h2>
                    <p class="mt-3 text-sm">
                        <span class="font-medium">{{ $studentProfile->academicSection?->name ?? '-' }}</span>
                        @if ($studentProfile->academicSection?->school_year)
                            <span class="text-neutral-500 dark:text-neutral-400">- {{ $studentProfile->academicSection->school_year }}</span>
                        @endif
                    </p>
                </div>

                <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                    <h2 class="text-lg font-semibold">{{ __('Teachers') }}</h2>

                    @if ($teachers->isEmpty())
                        <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No teachers linked to your section yet.') }}</p>
                    @else
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ($teachers as $teacher)
                                <li class="rounded-lg bg-neutral-100 px-3 py-2 dark:bg-neutral-800">
                                    {{ $teacher->name }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <h2 class="text-lg font-semibold">{{ __('Subjects') }}</h2>

                @if ($subjects->isEmpty())
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No subjects assigned to your section yet.') }}</p>
                @else
                    <ul class="mt-3 grid gap-2 md:grid-cols-2 lg:grid-cols-3 text-sm">
                        @foreach ($subjects as $subject)
                            <li class="rounded-lg bg-neutral-100 px-3 py-2 dark:bg-neutral-800">
                                <span class="font-medium">{{ $subject->name }}</span>
                                @if ($subject->code)
                                    <span class="text-neutral-500 dark:text-neutral-400">({{ $subject->code }})</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <h2 class="text-lg font-semibold">{{ __('Weekly schedule') }}</h2>

                @if ($schedules->isEmpty())
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No schedule blocks registered for your section yet.') }}</p>
                @else
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-neutral-200 text-left dark:border-neutral-700">
                                    <th class="px-3 py-2">{{ __('Day') }}</th>
                                    <th class="px-3 py-2">{{ __('Subject') }}</th>
                                    <th class="px-3 py-2">{{ __('Teacher') }}</th>
                                    <th class="px-3 py-2">{{ __('Hours') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schedules as $schedule)
                                    <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                        <td class="px-3 py-2">
                                            @php
                                                $translatedWeekday = __('ui.weekdays.'.$schedule->weekday);
                                            @endphp
                                            {{ $translatedWeekday === 'ui.weekdays.'.$schedule->weekday ? $schedule->weekday : $translatedWeekday }}
                                        </td>
                                        <td class="px-3 py-2">{{ $schedule->teachingAssignment?->subject?->name ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $schedule->teachingAssignment?->teacher?->name ?? '-' }}</td>
                                        <td class="px-3 py-2">
                                            {{ \Illuminate\Support\Str::substr((string) $schedule->start_time, 0, 5) }}
                                            -
                                            {{ \Illuminate\Support\Str::substr((string) $schedule->end_time, 0, 5) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <h2 class="text-lg font-semibold">{{ __('Recent assignments') }}</h2>

                @if ($recentAssignments->isEmpty())
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No assignments available yet.') }}</p>
                @else
                    <ul class="mt-3 space-y-3 text-sm">
                        @foreach ($recentAssignments as $assignment)
                            <li class="rounded-lg bg-neutral-100 px-3 py-3 dark:bg-neutral-800">
                                <p class="font-medium">{{ $assignment->title }}</p>
                                <p class="text-neutral-600 dark:text-neutral-300">
                                    {{ __('Subject') }}: {{ $assignment->teachingAssignment?->subject?->name ?? '-' }}
                                    |
                                    {{ __('Teacher') }}: {{ $assignment->teachingAssignment?->teacher?->name ?? '-' }}
                                </p>
                                <p class="text-neutral-500 dark:text-neutral-400">
                                    {{ __('Due date') }}:
                                    {{ $assignment->due_date?->format('Y-m-d') ?? __('Not defined') }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>
</x-layouts::app>
