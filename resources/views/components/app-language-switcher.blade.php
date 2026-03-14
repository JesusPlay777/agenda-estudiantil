@props([
    'id' => 'app-locale',
    'showLabel' => true,
    'labelClass' => 'mb-2 block text-xs font-medium text-zinc-600 dark:text-zinc-300',
    'selectClass' => 'w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/30 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100',
])

@php
    $supportedLocales = config('app.supported_locales', []);
@endphp

@if (count($supportedLocales) > 1)
    <form method="POST" action="{{ route('locale.switch') }}" {{ $attributes }}>
        @csrf

        @if ($showLabel)
            <label for="{{ $id }}" class="{{ $labelClass }}">{{ __('ui.locale.label') }}</label>
        @else
            <label for="{{ $id }}" class="sr-only">{{ __('ui.locale.label') }}</label>
        @endif

        <select
            id="{{ $id }}"
            name="locale"
            onchange="this.form.submit()"
            class="{{ $selectClass }}"
        >
            @foreach ($supportedLocales as $localeCode => $localeLabel)
                <option value="{{ $localeCode }}" @selected(app()->getLocale() === $localeCode)>
                    {{ $localeLabel }}
                </option>
            @endforeach
        </select>
    </form>
@endif
