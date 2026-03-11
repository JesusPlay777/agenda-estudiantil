@php
    $selectedTeachingAssignment = old('teaching_assignment_id', $assignment?->teaching_assignment_id);
@endphp

<div class="space-y-5">
    <div>
        <label for="teaching_assignment_id" class="mb-1 block text-sm font-medium">{{ __('Teaching assignment') }}</label>
        <select
            id="teaching_assignment_id"
            name="teaching_assignment_id"
            class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
            required
        >
            <option value="">{{ __('Select an option') }}</option>
            @foreach ($teachingAssignments as $teachingAssignment)
                <option value="{{ $teachingAssignment->id }}" @selected((string) $selectedTeachingAssignment === (string) $teachingAssignment->id)>
                    {{ __('Section') }}: {{ $teachingAssignment->academicSection?->name }} ({{ $teachingAssignment->academicSection?->school_year }})
                    |
                    {{ __('Subject') }}: {{ $teachingAssignment->subject?->name }}
                </option>
            @endforeach
        </select>
        @error('teaching_assignment_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="title" class="mb-1 block text-sm font-medium">{{ __('Title') }}</label>
        <input
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $assignment?->title) }}"
            class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
            maxlength="255"
            required
        />
        @error('title')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-medium">{{ __('Description') }}</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
            required
        >{{ old('description', $assignment?->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="due_date" class="mb-1 block text-sm font-medium">{{ __('Due date') }}</label>
            <input
                id="due_date"
                name="due_date"
                type="date"
                value="{{ old('due_date', $assignment?->due_date?->format('Y-m-d')) }}"
                class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
            />
            @error('due_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="published_at" class="mb-1 block text-sm font-medium">{{ __('Published at') }}</label>
            <input
                id="published_at"
                name="published_at"
                type="date"
                value="{{ old('published_at', $assignment?->published_at?->format('Y-m-d')) }}"
                class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
            />
            @error('published_at')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input
            id="is_active"
            name="is_active"
            type="hidden"
            value="0"
        />
        <input
            id="is_active"
            name="is_active"
            type="checkbox"
            value="1"
            class="h-4 w-4 rounded border-neutral-300 dark:border-neutral-700"
            @checked((bool) old('is_active', $assignment?->is_active ?? true))
        />
        <label for="is_active" class="text-sm">{{ __('Active') }}</label>
    </div>
</div>
