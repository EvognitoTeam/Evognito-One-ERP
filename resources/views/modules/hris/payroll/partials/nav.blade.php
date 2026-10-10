<div class="flex max-w-full gap-2 overflow-x-auto">
    <a href="{{ route('hris.payroll.index', ['organization' => $organization->slug]) }}" class="shrink-0 rounded-full px-4 py-2 text-[13px] font-semibold transition {{ request()->routeIs('hris.payroll.index') ? 'bg-[#252421] text-white' : 'border border-black/[0.07] bg-white text-[#77746d] hover:bg-[#f6f5f1]' }}">{{ __('hris.payroll.payroll_runs') }}</a>
    <a href="{{ route('hris.payroll.compensation', ['organization' => $organization->slug]) }}" class="shrink-0 rounded-full px-4 py-2 text-[13px] font-semibold transition {{ request()->routeIs('hris.payroll.compensation') ? 'bg-[#252421] text-white' : 'border border-black/[0.07] bg-white text-[#77746d] hover:bg-[#f6f5f1]' }}">{{ __('hris.payroll.compensation') }}</a>
</div>
