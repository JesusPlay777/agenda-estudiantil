<x-layouts::app :title="$assignment->title">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700 md:flex-row md:items-start md:justify-between">
            <div>
                <a
                    href="{{ route('student.assignments.index') }}"
                    class="inline-flex items-center text-sm text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white"
                    wire:navigate
                >
                    {{ __('Back to assignments') }}
                </a>

                <h1 class="mt-3 text-2xl font-semibold">{{ $assignment->title }}</h1>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                    {{ __('Subject') }}: {{ $assignment->teachingAssignment?->subject?->name ?? '-' }}
                    |
                    {{ __('Section') }}: {{ $assignment->teachingAssignment?->academicSection?->name ?? '-' }}
                </p>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Teacher') }}: {{ $assignment->teachingAssignment?->teacher?->name ?? '-' }}
                </p>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Due date') }}: {{ $assignment->due_date?->format('Y-m-d') ?? __('Not defined') }}
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full border px-2 py-1 text-xs font-medium {{ $submissionStatus['badge_class'] }}">
                {{ $submissionStatus['label'] }}
            </span>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <h2 class="text-lg font-semibold">{{ __('Assignment details') }}</h2>
            <p class="mt-3 whitespace-pre-line text-sm text-neutral-700 dark:text-neutral-200">{{ $assignment->description }}</p>
        </div>

        <form method="POST" action="{{ route('student.assignments.submit', $assignment) }}" class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            @csrf

            <h2 class="text-lg font-semibold">{{ __('Your submission') }}</h2>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                {{ __('Write your response and save it as your official submission.') }}
            </p>

            @if ($submission?->submitted_at)
                <p class="mt-3 rounded-lg bg-neutral-100 px-3 py-2 text-sm text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                    {{ __('Last submission') }}: {{ $submission->submitted_at->format('Y-m-d H:i') }}
                </p>
            @endif

            <div class="mt-4">
                <label for="submission_text" class="mb-1 block text-sm font-medium">{{ __('Submission text') }}</label>
                <textarea
                    id="submission_text"
                    name="submission_text"
                    rows="7"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                    maxlength="5000"
                    required
                >{{ old('submission_text', $submission?->submission_text) }}</textarea>
                @error('submission_text')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                >
                    {{ $submission ? __('Update submission') : __('Submit assignment') }}
                </button>
            </div>
        </form>

        @if ($submission?->reviewed_at || filled($submission?->score) || filled($submission?->teacher_feedback))
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <h2 class="text-lg font-semibold">{{ __('Teacher review') }}</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg bg-neutral-100 px-4 py-3 text-sm dark:bg-neutral-800">
                        <p class="text-neutral-500 dark:text-neutral-400">{{ __('Score') }}</p>
                        <p class="mt-1 text-lg font-semibold text-neutral-900 dark:text-white">
                            {{ filled($submission?->score) ? number_format((float) $submission->score, 2) : '—' }}
                        </p>
                    </div>

                    <div class="rounded-lg bg-neutral-100 px-4 py-3 text-sm dark:bg-neutral-800">
                        <p class="text-neutral-500 dark:text-neutral-400">{{ __('Reviewed at') }}</p>
                        <p class="mt-1 text-neutral-900 dark:text-white">
                            {{ $submission?->reviewed_at?->format('Y-m-d H:i') ?? '—' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 rounded-lg bg-neutral-100 px-4 py-3 text-sm dark:bg-neutral-800">
                    <p class="font-medium">{{ __('Teacher feedback') }}</p>
                    <p class="mt-2 whitespace-pre-line text-neutral-700 dark:text-neutral-200">
                        {{ $submission?->teacher_feedback ?: '—' }}
                    </p>
                </div>
            </div>
        @elseif ($submission?->submitted_at)
            <div class="rounded-xl border border-neutral-200 p-6 text-sm text-neutral-600 dark:border-neutral-700 dark:text-neutral-300">
                {{ __('Awaiting teacher review.') }}
            </div>
        @endif
    </div>
</x-layouts::app>
