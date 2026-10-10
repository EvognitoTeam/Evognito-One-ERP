@php
    $code = strtolower($module->code ?? '');
@endphp

@if (str_contains($code, 'hris') || str_contains($code, 'employee'))
    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
    </svg>
@elseif(str_contains($code, 'performance') || str_contains($code, 'kpi'))
    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
        <path d="M4 20V10" />
        <path d="M10 20V4" />
        <path d="M16 20v-7" />
        <path d="M22 20V7" />
    </svg>
@elseif(str_contains($code, 'lms') || str_contains($code, 'learning'))
    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
        <path d="m2 10 10-5 10 5-10 5L2 10Z" />
        <path d="M6 12v5c3 2 9 2 12 0v-5" />
    </svg>
@elseif(str_contains($code, 'kansiku') || str_contains($code, 'attendance'))
    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
        <circle cx="12" cy="12" r="9" />
        <path d="M12 7v5l3 2" />
        <path d="M8 3h8" />
    </svg>
@elseif(str_contains($code, 'sales') || str_contains($code, 'crm'))
    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
        <path d="M3 3v18h18" />
        <path d="m7 15 4-4 3 3 5-6" />
    </svg>
@elseif(str_contains($code, 'site') || str_contains($code, 'web'))
    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
        <circle cx="12" cy="12" r="9" />
        <path d="M3 12h18" />
        <path d="M12 3c2.5 2.7 3.8 5.7 3.8 9S14.5 18.3 12 21" />
        <path d="M12 3C9.5 5.7 8.2 8.7 8.2 12S9.5 18.3 12 21" />
    </svg>
@elseif(str_contains($code, 'commerce') || str_contains($code, 'store'))
    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
        <path d="M3 9 5 4h14l2 5" />
        <path d="M5 13v7h14v-7" />
        <path d="M3 9a3 3 0 0 0 6 0" />
        <path d="M9 9a3 3 0 0 0 6 0" />
        <path d="M15 9a3 3 0 0 0 6 0" />
    </svg>
@else
    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
        <rect x="3" y="3" width="7" height="7" rx="2" />
        <rect x="14" y="3" width="7" height="7" rx="2" />
        <rect x="3" y="14" width="7" height="7" rx="2" />
        <rect x="14" y="14" width="7" height="7" rx="2" />
    </svg>
@endif
