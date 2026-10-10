<section class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
    <div>
        <div class="text-xs font-bold uppercase tracking-[0.12em] text-purple-700">Performance</div>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-[#252421]">{{ $title }}</h1>
        @if(!empty($description))
            <p class="mt-3 max-w-3xl text-sm leading-6 text-[#77746d]">{{ $description }}</p>
        @endif
    </div>
    @include('modules.performance.partials.language-switcher')
</section>
<div class="mt-8">@include('modules.performance.partials.navigation')</div>
