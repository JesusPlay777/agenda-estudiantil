<x-layouts::app :title="__('Teacher Dashboard')">
    <div class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">{{ __('Teacher Dashboard') }}</h1>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                        {{ __('Review your assigned subjects, sections, weekly schedule, and recent assignments.') }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('teacher.assignments.index') }}"
                        class="inline-flex items-center rounded-lg border border-neutral-300 px-3 py-2 text-sm transition hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800"
                        wire:navigate
                    >
                        {{ __('View assignments') }}
                    </a>
                    <a
                        href="{{ route('teacher.assignments.create') }}"
                        class="inline-flex items-center rounded-lg bg-neutral-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                        wire:navigate
                    >
                        {{ __('Create assignment') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <h2 class="text-lg font-semibold">{{ __('Assigned subjects') }}</h2>

                @if ($subjects->isEmpty())
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No subjects assigned yet.') }}</p>
                @else
                    <ul class="mt-3 space-y-2 text-sm">
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
                <h2 class="text-lg font-semibold">{{ __('Assigned sections') }}</h2>

                @if ($sections->isEmpty())
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No sections assigned yet.') }}</p>
                @else
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($sections as $section)
                            <li class="rounded-lg bg-neutral-100 px-3 py-2 dark:bg-neutral-800">
                                <span class="font-medium">{{ $section->name }}</span>
                                <span class="text-neutral-500 dark:text-neutral-400">- {{ $section->school_year }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <h2 class="text-lg font-semibold">{{ __('Weekly schedule') }}</h2>

            @if ($schedules->isEmpty())
                <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No schedule blocks registered yet.') }}</p>
            @else
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left dark:border-neutral-700">
                                <th class="px-3 py-2">{{ __('Day') }}</th>
                                <th class="px-3 py-2">{{ __('Subject') }}</th>
                                <th class="px-3 py-2">{{ __('Section') }}</th>
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
                                    <td class="px-3 py-2">
                                        @php
                                            $section = $schedule->teachingAssignment?->academicSection;
                                        @endphp
                                        {{ $section ? $section->name.' - '.$section->school_year : '-' }}
                                    </td>
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
                <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No assignments published yet.') }}</p>
            @else
                <ul class="mt-3 space-y-3 text-sm">
                    @foreach ($recentAssignments as $assignment)
                        <li class="rounded-lg bg-neutral-100 px-3 py-3 dark:bg-neutral-800">
                            <p class="font-medium">{{ $assignment->title }}</p>
                            <p class="text-neutral-600 dark:text-neutral-300">
                                {{ __('Subject') }}: {{ $assignment->teachingAssignment?->subject?->name ?? '-' }}
                                |
                                {{ __('Section') }}: {{ $assignment->teachingAssignment?->academicSection?->name ?? '-' }}
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
    </div>
</x-layouts::app>
