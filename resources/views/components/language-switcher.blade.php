@php
    $currentLocale = app()->getLocale();
@endphp

<form method="POST" action="{{ route('language.update') }}" class="relative">
    @csrf
    <label class="sr-only" for="workspace-language">{{ __('common.language') }}</label>
    <select
        id="workspace-language"
        name="locale"
        onchange="this.form.submit()"
        class="h-10 rounded-xl border border-black/[0.06] bg-white px-3 pr-8 text-xs font-semibold text-[#55524c] outline-none transition hover:bg-[#f6f5f1] focus:border-blue-200 focus:ring-2 focus:ring-blue-50"
        title="{{ __('common.language') }}"
    >
        <option value="id" @selected($currentLocale === 'id')>ID · Bahasa Indonesia</option>
        <option value="en" @selected($currentLocale === 'en')>EN · English</option>
    </select>
</form>
