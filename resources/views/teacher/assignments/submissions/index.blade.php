<x-layouts::app :title="__('Assignment submissions')">
    <div class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <a
                href="{{ route('teacher.assignments.index') }}"
                class="inline-flex items-center text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white"
                wire:navigate
            >
                {{ __('Back to assignments') }}
            </a>

            <h1 class="mt-3 text-2xl font-semibold">{{ __('Assignment submissions') }}</h1>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                {{ __('Review student submissions for this assignment.') }}
            </p>
            <p class="mt-3 text-sm text-neutral-700 dark:text-neutral-200">
                <span class="font-medium">{{ $assignment->title }}</span>
                |
                {{ __('Subject') }}: {{ $assignment->teachingAssignment?->subject?->name ?? '-' }}
                |
                {{ __('Section') }}: {{ $assignment->teachingAssignment?->academicSection?->name ?? '-' }}
            </p>

            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                <span class="rounded-full bg-neutral-100 px-3 py-1 dark:bg-neutral-800">
                    {{ __('Submissions') }}: {{ $reviewStats['submitted'] }}
                </span>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-100">
                    {{ __('Reviewed') }}: {{ $reviewStats['reviewed'] }}
                </span>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-800 dark:bg-amber-900/20 dark:text-amber-100">
                    {{ __('Pending review') }}: {{ $reviewStats['pending_review'] }}
                </span>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($students->isEmpty())
            <div class="rounded-xl border border-neutral-200 p-6 text-sm text-neutral-600 dark:border-neutral-700 dark:text-neutral-300">
                {{ __('No students found in this section.') }}
            </div>
        @else
            <div class="space-y-3">
                @foreach ($students as $entry)
                    @php
                        $student = $entry['student'];
                        $submission = $entry['submission'];
                        $status = $entry['status'];
                        $reviewStatus = $entry['review_status'];
                    @endphp
                    <article class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">{{ $student?->name ?? '-' }}</h2>
                                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">
                                    {{ $student?->email ?? '-' }}
                                </p>
                                @if ($submission?->submitted_at)
                                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ __('Last submission') }}: {{ $submission->submitted_at->format('Y-m-d H:i') }}
                                    </p>
                                @else
                                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ __('Awaiting student submission.') }}
                                    </p>
                                @endif

                                @if (filled($submission?->score))
                                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ __('Score') }}: {{ number_format((float) $submission->score, 2) }}
                                    </p>
                                @endif
                                @if ($submission?->attachments?->isNotEmpty())
                                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ __('Attachments') }}: {{ $submission->attachments->count() }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-col items-start gap-2 md:items-end">
                                <span class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-medium {{ $status['badge_class'] }}">
                                    {{ $status['label'] }}
                                </span>
                                <span class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-medium {{ $reviewStatus['badge_class'] }}">
                                    {{ $reviewStatus['label'] }}
                                </span>

                                @if ($submission?->submitted_at)
                                    <a
                                        href="{{ route('teacher.assignments.submissions.show', [$assignment, $submission]) }}"
                                        class="inline-flex items-center rounded-lg border border-neutral-300 px-3 py-2 text-sm transition hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800"
                                        wire:navigate
                                    >
                                        {{ __('Review submission') }}
                                    </a>
                                @else
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('No submission yet.') }}</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts::app>
