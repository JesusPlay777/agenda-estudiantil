<x-layouts::app :title="__('My assignments')">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ __('My assignments') }}</h1>
                <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                    {{ __('Manage tasks for your assigned sections and subjects.') }}
                </p>
            </div>

            <a
                href="{{ route('teacher.assignments.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                wire:navigate
            >
                {{ __('Create assignment') }}
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($assignments->isEmpty())
            <div class="rounded-xl border border-neutral-200 p-6 text-sm text-neutral-600 dark:border-neutral-700 dark:text-neutral-300">
                {{ __('No assignments created yet.') }}
            </div>
        @else
            <div class="space-y-3">
                @foreach ($assignments as $assignment)
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
                                    {{ __('Due date') }}: {{ $assignment->due_date?->format('Y-m-d') ?? __('Not defined') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <a
                                    href="{{ route('teacher.assignments.edit', $assignment) }}"
                                    class="inline-flex items-center rounded-lg border border-neutral-300 px-3 py-2 text-sm transition hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800"
                                    wire:navigate
                                >
                                    {{ __('Edit') }}
                                </a>

                                <form method="POST" action="{{ route('teacher.assignments.destroy', $assignment) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-lg border border-red-300 px-3 py-2 text-sm text-red-700 transition hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-900/20"
                                        onclick="return confirm('{{ __('Are you sure you want to delete this assignment?') }}')"
                                    >
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>
                {{ $assignments->links() }}
            </div>
        @endif
    </div>
</x-layouts::app>
