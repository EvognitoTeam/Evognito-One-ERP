@php
    $integration = $integration ?? null;
@endphp

@if ($errors->any())
    <div
        class="mb-6 rounded-xl
               border border-red-200
               bg-red-50 p-4
               text-[13px]
               text-red-700">
        @foreach ($errors->all() as $error)
            <div>• {{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="grid gap-5
           xl:grid-cols-[1fr_370px]">
    <div class="space-y-5">

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px]
                       font-semibold">
                Integration Information
            </h2>

            <p class="mt-1 text-[12px]
                       text-[#99968e]">
                Informasi provider atau
                external service.
            </p>

            <div class="mt-6 grid gap-5
                       md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Name
                    </label>

                    <input type="text" id="integration-name" name="name" required
                        value="{{ old('name', $integration?->name) }}"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Code
                    </label>

                    <input type="text" id="integration-code" name="code" required
                        value="{{ old('code', $integration?->code) }}"
                        placeholder="onesignal"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Category
                    </label>

                    <select name="category"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                        @foreach ([
        'payment' => 'Payment',
        'messaging' => 'Messaging',
        'email' => 'Email',
        'storage' => 'Storage',
        'identity' => 'Identity / SSO',
        'analytics' => 'Analytics',
        'webhook' => 'Webhook',
        'other' => 'Other',
    ] as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', $integration?->category ?? 'other') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Provider
                    </label>

                    <input type="text" name="provider"
                        value="{{ old('provider', $integration?->provider) }}"
                        placeholder="OneSignal, AWS, Google..."
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Environment
                    </label>

                    <select name="environment"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                        @foreach ([
        'sandbox' => 'Sandbox',
        'production' => 'Production',
        'internal' => 'Internal',
    ] as $value => $label)
                            <option value="{{ $value }}" @selected(old('environment', $integration?->environment ?? 'production') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Status
                    </label>

                    <select name="status"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                        <option value="active" @selected(old('status', $integration?->status ?? 'inactive') === 'active')>
                            Active
                        </option>

                        <option value="inactive" @selected(old('status', $integration?->status ?? 'inactive') === 'inactive')>
                            Inactive
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-[13px] font-medium">
                        Base URL
                    </label>

                    <input type="url" name="base_url"
                        value="{{ old('base_url', $integration?->base_url) }}"
                        placeholder="https://api.example.com"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-[13px] font-medium">
                        Webhook URL
                    </label>

                    <input type="url" name="webhook_url"
                        value="{{ old('webhook_url', $integration?->webhook_url) }}"
                        placeholder="https://..."
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3.5 text-[13px]">
                </div>
            </div>
        </section>

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px] font-semibold">
                Credentials
            </h2>

            <p class="mt-1 text-[12px]
                       leading-5
                       text-[#99968e]">
                Credential disimpan terenkripsi.
                Pada edit, kosongkan field yang
                tidak ingin diubah.
            </p>

            @if ($integration && $integration->hasCredentials())
                <div
                    class="mt-4 rounded-xl
                           bg-emerald-50
                           px-4 py-3
                           text-[12px]
                           text-emerald-700">
                    Credential tersimpan:
                    {{ implode(', ', $integration->credentialKeys()) }}
                </div>
            @endif

            <div class="mt-6 grid gap-5
                       md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        API Key
                    </label>

                    <input type="password" name="api_key" autocomplete="new-password"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Secret Key
                    </label>

                    <input type="password" name="secret_key" autocomplete="new-password"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Client ID
                    </label>

                    <input type="password" name="client_id" autocomplete="new-password"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Client Secret
                    </label>

                    <input type="password" name="client_secret" autocomplete="new-password"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Username
                    </label>

                    <input type="text" name="username" autocomplete="off"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>

                <div>
                    <label class="mb-2 block text-[13px] font-medium">
                        Password
                    </label>

                    <input type="password" name="integration_password" autocomplete="new-password"
                        class="h-11 w-full rounded-xl border border-black/[0.09] bg-[#faf9f6] px-3 text-[13px]">
                </div>
            </div>

            @if ($integration && $integration->hasCredentials())
                <label
                    class="mt-5 flex
                           items-center gap-3
                           text-[12px]
                           text-red-700">
                    <input type="checkbox" name="clear_credentials" value="1">

                    Remove all saved credentials
                </label>
            @endif
        </section>

        <section class="rounded-2xl
                   border border-black/[0.07]
                   bg-white p-6">
            <h2 class="text-[16px] font-semibold">
                Advanced Configuration
            </h2>

            <div class="mt-5">
                <label class="mb-2 block text-[13px] font-medium">
                    Configuration JSON
                </label>

                <textarea name="config_json" rows="8" placeholder='{"region":"ap-southeast-1"}'
                    class="w-full resize-y rounded-xl border border-black/[0.09] bg-[#faf9f6] p-3.5 font-mono text-[12px]">{{ old(
                        'config_json',
                        $integration?->config ? json_encode($integration->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '',
                    ) }}</textarea>

                <p class="mt-2 text-[11px]
                           text-[#99968e]">
                    Jangan masukkan password,
                    token atau secret ke sini.
                </p>
            </div>

            <div class="mt-5">
                <label class="mb-2 block text-[13px] font-medium">
                    Notes
                </label>

                <textarea name="notes" rows="4"
                    class="w-full resize-none rounded-xl border border-black/[0.09] bg-[#faf9f6] p-3.5 text-[13px]">{{ old('notes', $integration?->notes) }}</textarea>
            </div>
        </section>

    </div>

    <div>
        <section
            class="sticky top-[96px]
                   rounded-2xl
                   bg-[#1d1d1b]
                   p-6 text-white">
            <div
                class="text-[11px]
                       font-semibold uppercase
                       tracking-[.1em]
                       text-white/40">
                Integration
            </div>

            <h2 class="mt-4 text-[21px]
                       font-semibold">
                Secure connector
            </h2>

            <p class="mt-3 text-[13px]
                       leading-6
                       text-white/50">
                Credentials menggunakan
                encryption Laravel dan tidak
                ditampilkan kembali ke halaman.
            </p>

            <button type="submit"
                class="mt-7 h-11 w-full
                       rounded-xl bg-white
                       text-[13px]
                       font-semibold text-black">
                {{ $submitLabel ?? 'Save Integration' }}
            </button>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const name =
            document.getElementById(
                'integration-name'
            );

        const code =
            document.getElementById(
                'integration-code'
            );

        if (!name || !code) {
            return;
        }

        let manuallyEdited =
            code.value.trim() !== '';

        code.addEventListener(
            'input',
            () => {
                manuallyEdited =
                    code.value.trim() !== '';
            }
        );

        name.addEventListener(
            'input',
            () => {
                if (manuallyEdited) {
                    return;
                }

                code.value =
                    name.value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(
                        /[\u0300-\u036f]/g,
                        ''
                    )
                    .replace(
                        /[^a-z0-9]+/g,
                        '_'
                    )
                    .replace(
                        /^_+|_+$/g,
                        ''
                    );
            }
        );
    });
</script>
