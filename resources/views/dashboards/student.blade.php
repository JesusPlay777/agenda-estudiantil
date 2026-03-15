<x-layouts::app :title="__('Student Dashboard')">
    <div class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">{{ __('Student Dashboard') }}</h1>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                        {{ __('Review your section, subjects, weekly schedule, and assigned tasks.') }}
                    </p>
                </div>

                <a
                    href="{{ route('student.assignments.index') }}"
                    class="inline-flex items-center rounded-lg border border-neutral-300 px-3 py-2 text-sm transition hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800"
                    wire:navigate
                >
                    {{ __('View assignments') }}
                </a>
            </div>
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
                    <h2 class="text-lg font-semibold">{{ __('Subjects and teachers') }}</h2>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                        {{ __('Each subject appears with its assigned teacher.') }}
                    </p>

                    @if ($subjectTeachers->isEmpty())
                        <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No subjects assigned to your section yet.') }}</p>
                    @else
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ($subjectTeachers as $subjectTeacher)
                                <li class="rounded-lg bg-neutral-100 px-3 py-2 dark:bg-neutral-800">
                                    <span class="font-medium">{{ $subjectTeacher->subject?->name ?? '-' }}</span>
                                    @if ($subjectTeacher->subject?->code)
                                        <span class="text-neutral-500 dark:text-neutral-400">({{ $subjectTeacher->subject->code }})</span>
                                    @endif
                                    <span class="text-neutral-500 dark:text-neutral-400">- {{ $subjectTeacher->teacher?->name ?? '-' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <h2 class="text-lg font-semibold">{{ __('Weekly schedule') }}</h2>

                @if ($schedules->isEmpty())
                    <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">{{ __('No schedule blocks registered for your section yet.') }}</p>
                @else
                    <div class="mt-4 grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
                        @foreach ($scheduleGroups as $scheduleGroup)
                            @php
                                $weekday = $scheduleGroup['weekday'];
                                $groupedSchedules = $scheduleGroup['schedules'];
                                $translatedWeekday = __('ui.weekdays.'.$weekday);
                            @endphp
                            <section class="overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50/70 dark:border-neutral-700 dark:bg-neutral-900/30">
                                <header class="border-b border-neutral-200 px-4 py-3 dark:border-neutral-700">
                                    <h3 class="font-semibold">
                                        {{ $translatedWeekday === 'ui.weekdays.'.$weekday ? $weekday : $translatedWeekday }}
                                    </h3>
                                </header>

                                @if ($groupedSchedules->isEmpty())
                                    <p class="px-4 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ __('No classes scheduled.') }}
                                    </p>
                                @else
                                    <ul class="divide-y divide-neutral-200 text-sm dark:divide-neutral-700">
                                        @foreach ($groupedSchedules as $schedule)
                                            <li class="px-4 py-3">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="font-medium">{{ $schedule->teachingAssignment?->subject?->name ?? '-' }}</p>
                                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                            {{ __('Teacher') }}: {{ $schedule->teachingAssignment?->teacher?->name ?? '-' }}
                                                        </p>
                                                    </div>
                                                    <span class="rounded-md bg-white px-2 py-1 text-xs font-medium text-neutral-700 shadow-sm dark:bg-neutral-800 dark:text-neutral-200">
                                                        {{ \Illuminate\Support\Str::substr((string) $schedule->start_time, 0, 5) }}
                                                        -
                                                        {{ \Illuminate\Support\Str::substr((string) $schedule->end_time, 0, 5) }}
                                                    </span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </section>
                        @endforeach
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
                            @php
                                $submission = $recentSubmissionsByAssignment->get($assignment->id);
                                $status = $recentStatusesByAssignment->get($assignment->id);
                            @endphp
                            <li class="rounded-lg bg-neutral-100 px-3 py-3 dark:bg-neutral-800">
                                <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                    <p class="font-medium">{{ $assignment->title }}</p>
                                    @if ($status)
                                        <span class="inline-flex w-fit items-center rounded-full border px-2 py-1 text-xs font-medium {{ $status['badge_class'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-neutral-600 dark:text-neutral-300">
                                    {{ __('Subject') }}: {{ $assignment->teachingAssignment?->subject?->name ?? '-' }}
                                    |
                                    {{ __('Teacher') }}: {{ $assignment->teachingAssignment?->teacher?->name ?? '-' }}
                                </p>
                                <p class="text-neutral-500 dark:text-neutral-400">
                                    {{ __('Due date') }}:
                                    {{ $assignment->due_date?->format('Y-m-d') ?? __('Not defined') }}
                                </p>
                                @if ($submission?->submitted_at)
                                    <p class="text-neutral-500 dark:text-neutral-400">
                                        {{ __('Last submission') }}: {{ $submission->submitted_at->format('Y-m-d H:i') }}
                                    </p>
                                @endif
                                <a
                                    href="{{ route('student.assignments.show', $assignment) }}"
                                    class="mt-2 inline-flex items-center rounded-lg border border-neutral-300 px-3 py-1.5 text-xs transition hover:bg-neutral-200 dark:border-neutral-700 dark:hover:bg-neutral-700"
                                    wire:navigate
                                >
                                    {{ __('View details') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>
</x-layouts::app>
