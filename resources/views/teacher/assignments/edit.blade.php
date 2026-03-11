<x-layouts::app :title="__('Edit assignment')">
    <div class="space-y-6">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <h1 class="text-2xl font-semibold">{{ __('Edit assignment') }}</h1>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">
                {{ __('Update the selected task.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('teacher.assignments.update', $assignment) }}" class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            @csrf
            @method('PUT')

            @include('teacher.assignments._form', ['assignment' => $assignment, 'teachingAssignments' => $teachingAssignments])

            <div class="mt-6 flex items-center gap-3">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                >
                    {{ __('Save changes') }}
                </button>

                <a
                    href="{{ route('teacher.assignments.index') }}"
                    class="inline-flex items-center rounded-lg border border-neutral-300 px-4 py-2 text-sm transition hover:bg-neutral-100 dark:border-neutral-700 dark:hover:bg-neutral-800"
                    wire:navigate
                >
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</x-layouts::app>
