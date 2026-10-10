<form method="POST" action="{{ url('/language') }}">
    @csrf
    <select name="locale" onchange="this.form.submit()"
        class="h-10 rounded-xl border border-black/[0.08] bg-white px-3 text-sm font-semibold text-[#55524c]"
        aria-label="{{ __('performance.common.language') }}">
        <option value="id" @selected(app()->getLocale() === 'id')>Indonesia</option>
        <option value="en" @selected(app()->getLocale() === 'en')>English</option>
    </select>
</form>
