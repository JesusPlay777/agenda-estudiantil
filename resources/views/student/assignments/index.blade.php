<x-layouts::app :title="__('My assignments')">
    <div class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <h1 class="text-2xl font-semibold">{{ __('My assignments') }}</h1>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                {{ __('Review assignments for your section and track your submission status.') }}
            </p>
        </div>

        @if (! $studentProfile)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-100">
                {{ __('Your student profile is not configured yet. Contact an administrator to assign your section.') }}
            </div>
        @elseif ($assignments->isEmpty())
            <div class="rounded-xl border border-neutral-200 p-6 text-sm text-neutral-600 dark:border-neutral-700 dark:text-neutral-300">
                {{ __('No assignments available yet.') }}
            </div>
        @else
            <div class="space-y-3">
                @foreach ($assignments as $assignment)
                    @php
                        $submission = $submissionsByAssignment->get($assignment->id);
                        $status = $statusesByAssignment->get($assignment->id);
                    @endphp

                    <article class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">{{ $assignment->title }}</h2>
                                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">
                                    {{ __('Subject') }}: {{ $assignment->teachingAssignment?->subject?->name ?? '-' }}
                                    |
                                    {{ __('Section') }}: {{ $assignment->teachingAssignment?->academicSection?->name ?? '-' }}
                                </p>
                                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ __('Teacher') }}: {{ $assignment->teachingAssignment?->teacher?->name ?? '-' }}
                                </p>
                                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ __('Due date') }}:
                                    {{ $assignment->due_date?->format('Y-m-d') ?? __('Not defined') }}
                                </p>
                                @if ($submission?->submitted_at)
                                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ __('Last submission') }}: {{ $submission->submitted_at->format('Y-m-d H:i') }}
                                    </p>
                                @endif
                                @if ($submission?->reviewed_at)
                                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ __('Score') }}: {{ number_format((float) $submission->score, 2) }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-col items-start gap-2 md:items-end">
                                @if ($status)
                                    <span class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-medium {{ $status['badge_class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                @endif

                                <a
                                    href="{{ route('student.assignments.show', $assignment) }}"
                                    class="inline-flex items-center rounded-lg border border-neutral-300 px-3 py-2 text-sm transition hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800"
                                    wire:navigate
                                >
                                    {{ __('View details') }}
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if (method_exists($assignments, 'links'))
                <div>
                    {{ $assignments->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts::app>
