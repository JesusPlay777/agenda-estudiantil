<x-layouts::app :title="__('Submission review')">
    <div class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <a
                href="{{ route('teacher.assignments.submissions.index', $assignment) }}"
                class="inline-flex items-center text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white"
                wire:navigate
            >
                {{ __('Back to assignments') }}
            </a>

            <h1 class="mt-3 text-2xl font-semibold">{{ __('Submission review') }}</h1>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                {{ __('Review student submissions for this assignment.') }}
            </p>
            <p class="mt-3 text-sm text-neutral-700 dark:text-neutral-200">
                <span class="font-medium">{{ $assignment->title }}</span>
                |
                {{ __('Student') }}: {{ $submission->student?->name ?? '-' }}
                |
                {{ __('Subject') }}: {{ $assignment->teachingAssignment?->subject?->name ?? '-' }}
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">{{ __('Your submission') }}</h2>
                    <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                        {{ __('Last submission') }}: {{ $submission->submitted_at?->format('Y-m-d H:i') ?? __('Not defined') }}
                    </p>
                </div>

                <span class="inline-flex w-fit items-center rounded-full border px-2 py-1 text-xs font-medium {{ $submissionStatus['badge_class'] }}">
                    {{ $submissionStatus['label'] }}
                </span>
            </div>

            <div class="mt-3">
                <span class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-medium {{ $reviewStatus['badge_class'] }}">
                    {{ $reviewStatus['label'] }}
                </span>
            </div>

            <div class="mt-4 rounded-lg bg-neutral-100 p-4 text-sm text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                <p class="whitespace-pre-line">{{ $submission->submission_text }}</p>
            </div>

            @if ($submission->attachments->isNotEmpty())
                <div class="mt-4 rounded-lg bg-neutral-100 p-4 text-sm dark:bg-neutral-800">
                    <p class="font-medium">{{ __('Attachments') }}</p>
                    <ul class="mt-2 space-y-2">
                        @foreach ($submission->attachments as $attachment)
                            <li class="flex items-center justify-between gap-3">
                                <span class="truncate text-neutral-700 dark:text-neutral-200">{{ $attachment->original_name }}</span>
                                <a
                                    href="{{ route('submission-attachments.download', $attachment) }}"
                                    class="inline-flex items-center rounded-lg border border-neutral-300 px-3 py-1.5 text-xs transition hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-700"
                                >
                                    {{ __('Download') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('teacher.assignments.submissions.review', [$assignment, $submission]) }}" class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            @csrf
            @method('PUT')

            <h2 class="text-lg font-semibold">{{ __('Teacher review') }}</h2>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                {{ __('Add a score and feedback for this submission.') }}
            </p>

            @if ($submission->reviewed_at)
                <p class="mt-3 rounded-lg bg-neutral-100 px-3 py-2 text-sm text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                    {{ __('Reviewed at') }}: {{ $submission->reviewed_at->format('Y-m-d H:i') }}
                    @if ($submission->reviewedBy?->name)
                        | {{ __('Reviewed by') }}: {{ $submission->reviewedBy->name }}
                    @endif
                </p>
            @endif

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="score" class="mb-1 block text-sm font-medium">{{ __('Score') }}</label>
                    <input
                        id="score"
                        name="score"
                        type="number"
                        min="0"
                        max="20"
                        step="0.01"
                        value="{{ old('score', $submission->score) }}"
                        class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                    />
                    @error('score')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <label for="teacher_feedback" class="mb-1 block text-sm font-medium">{{ __('Teacher feedback') }}</label>
                <textarea
                    id="teacher_feedback"
                    name="teacher_feedback"
                    rows="6"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                    maxlength="5000"
                >{{ old('teacher_feedback', $submission->teacher_feedback) }}</textarea>
                @error('teacher_feedback')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                >
                    {{ __('Save review') }}
                </button>

                @if ($submission->reviewed_at || filled($submission->score) || filled($submission->teacher_feedback))
                    <form method="POST" action="{{ route('teacher.assignments.submissions.clear-review', [$assignment, $submission]) }}">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-900/20"
                            onclick="return confirm('{{ __('Are you sure you want to clear this review?') }}')"
                        >
                            {{ __('Clear review') }}
                        </button>
                    </form>
                @endif
            </div>
        </form>
    </div>
</x-layouts::app>
