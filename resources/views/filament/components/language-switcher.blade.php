@php
    $supportedLocales = config('app.supported_locales', []);
    $currentLocale = app()->getLocale();
@endphp

@if (count($supportedLocales) > 1)
    <form method="POST" action="{{ route('locale.switch') }}" class="me-3 hidden md:block">
        @csrf

        <label for="filament-locale" class="sr-only">{{ __('ui.locale.label') }}</label>

        <select
            id="filament-locale"
            name="locale"
            onchange="this.form.submit()"
            class="block w-36 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
        >
            @foreach ($supportedLocales as $localeCode => $localeLabel)
                <option value="{{ $localeCode }}" @selected($currentLocale === $localeCode)>
                    {{ $localeLabel }}
                </option>
            @endforeach
        </select>
    </form>
@endif
