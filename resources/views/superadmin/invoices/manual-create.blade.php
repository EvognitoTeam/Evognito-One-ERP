@extends('layouts.superadmin')

@section('title', 'Create Custom Invoice')
@section('page_name', 'Create Custom Invoice')

@section('content')

    <div class="flex flex-col gap-5
           lg:flex-row
           lg:items-end
           lg:justify-between">
        <div>
            <a href="{{ route('superadmin.invoices.index') }}"
                class="text-[12px]
                   font-semibold
                   text-[#88857d]">
                ← Invoices
            </a>

            <h1 class="mt-4 text-[30px]
                   font-semibold
                   tracking-[-.045em]">
                Create Custom Invoice
            </h1>

            <p
                class="mt-2 max-w-2xl
                   text-[14px]
                   leading-6
                   text-[#85827b]">
                Buat invoice langsung dari
                Evognito untuk layanan,
                maintenance, setup, development,
                hardware atau biaya lainnya.
            </p>
        </div>

        <div
            class="rounded-full
               bg-orange-50
               px-3.5 py-2
               text-[12px]
               font-semibold
               text-orange-700">
            Manual Invoice
        </div>
    </div>


    @if ($errors->any())
        <div
            class="mt-6 rounded-xl
               border border-red-200
               bg-red-50 p-4
               text-[13px]
               text-red-700">
            @foreach ($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif


    <form method="POST" action="{{ route('superadmin.invoices.manual.store') }}" class="mt-7">
        @csrf

        <div class="grid gap-5
               xl:grid-cols-[1fr_370px]">
            <div class="space-y-5">

                {{-- INVOICE INFORMATION --}}
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[16px]
                           font-semibold">
                        Invoice Information
                    </h2>

                    <div class="mt-6 grid gap-5
                           md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label
                                class="mb-2 block
                                   text-[13px]
                                   font-medium">
                                Organization
                            </label>

                            <select name="organization_id" required
                                class="h-11 w-full
                                   rounded-xl
                                   border border-black/[0.09]
                                   bg-[#faf9f6]
                                   px-3
                                   text-[13px]">
                                <option value="">
                                    Select organization
                                </option>

                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}" @selected(old('organization_id') == $organization->id)>
                                        {{ $organization->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="mb-2 block
                                   text-[13px]
                                   font-medium">
                                Invoice Title
                            </label>

                            <input type="text" name="title" required
                                value="{{ old('title') }}"
                                placeholder="Contoh: Setup & Implementation Fee"
                                class="h-11 w-full
                                   rounded-xl
                                   border border-black/[0.09]
                                   bg-[#faf9f6]
                                   px-3.5
                                   text-[13px]">
                        </div>

                        <div>
                            <label
                                class="mb-2 block
                                   text-[13px]
                                   font-medium">
                                Service Period From
                            </label>

                            <input type="date" name="period_start"
                                value="{{ old('period_start') }}"
                                class="h-11 w-full
                                   rounded-xl
                                   border border-black/[0.09]
                                   bg-[#faf9f6]
                                   px-3
                                   text-[13px]">
                        </div>

                        <div>
                            <label
                                class="mb-2 block
                                   text-[13px]
                                   font-medium">
                                Service Period To
                            </label>

                            <input type="date" name="period_end"
                                value="{{ old('period_end') }}"
                                class="h-11 w-full
                                   rounded-xl
                                   border border-black/[0.09]
                                   bg-[#faf9f6]
                                   px-3
                                   text-[13px]">
                        </div>

                        <div>
                            <label
                                class="mb-2 block
                                   text-[13px]
                                   font-medium">
                                Due Date
                            </label>

                            <input type="datetime-local" name="due_at"
                                value="{{ old('due_at', now()->addDays($invoiceDueDays)->format('Y-m-d\TH:i')) }}"
                                class="h-11 w-full
                                   rounded-xl
                                   border border-black/[0.09]
                                   bg-[#faf9f6]
                                   px-3
                                   text-[13px]">
                        </div>

                        <div class="flex items-end">
                            <label
                                class="flex h-11
                                   items-center
                                   gap-3
                                   text-[13px]">
                                <input type="checkbox" name="issue_now" value="1" checked>

                                Issue invoice immediately
                            </label>
                        </div>
                    </div>
                </section>


                {{-- ITEMS --}}
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <div class="flex items-start
                           justify-between gap-4">
                        <div>
                            <h2 class="text-[16px]
                                   font-semibold">
                                Invoice Items
                            </h2>

                            <p
                                class="mt-1
                                   text-[12px]
                                   text-[#99968e]">
                                Item biaya akan
                                ditampilkan satu per satu
                                pada invoice.
                            </p>
                        </div>

                        <button type="button" id="add-invoice-item"
                            class="h-10
                               rounded-xl
                               border
                               border-black/[0.08]
                               bg-[#faf9f6]
                               px-4
                               text-[12px]
                               font-semibold">
                            + Add Item
                        </button>
                    </div>

                    <div id="invoice-items" class="mt-6 space-y-3">
                        @php
                            $oldItems = old('items', [
                                [
                                    'code' => '',
                                    'description' => '',
                                    'quantity' => 1,
                                    'unit_price' => 0,
                                ],
                            ]);
                        @endphp

                        @foreach ($oldItems as $index => $item)
                            <div
                                class="invoice-item
                                   grid gap-3
                                   rounded-xl
                                   border border-black/[0.07]
                                   bg-[#faf9f6]
                                   p-4
                                   md:grid-cols-[120px_1fr_110px_160px_42px]">
                                <div>
                                    <label
                                        class="mb-2 block
                                           text-[11px]
                                           font-medium">
                                        Code
                                    </label>

                                    <input type="text" name="items[{{ $index }}][code]"
                                        value="{{ $item['code'] ?? '' }}" placeholder="SETUP"
                                        class="h-10 w-full
                                           rounded-lg
                                           border
                                           border-black/[0.08]
                                           bg-white
                                           px-3
                                           text-[12px]">
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block
                                           text-[11px]
                                           font-medium">
                                        Description
                                    </label>

                                    <input type="text" name="items[{{ $index }}][description]"
                                        value="{{ $item['description'] ?? '' }}" required
                                        placeholder="Setup & implementation"
                                        class="h-10 w-full
                                           rounded-lg
                                           border
                                           border-black/[0.08]
                                           bg-white
                                           px-3
                                           text-[12px]">
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block
                                           text-[11px]
                                           font-medium">
                                        Qty
                                    </label>

                                    <input type="number" min="0.01" step="0.01"
                                        name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}"
                                        required
                                        class="item-quantity
                                           h-10 w-full
                                           rounded-lg
                                           border
                                           border-black/[0.08]
                                           bg-white
                                           px-3
                                           text-[12px]">
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block
                                           text-[11px]
                                           font-medium">
                                        Unit Price
                                    </label>

                                    <input type="number" min="0" step="1"
                                        name="items[{{ $index }}][unit_price]"
                                        value="{{ $item['unit_price'] ?? 0 }}" required
                                        class="item-price
                                           h-10 w-full
                                           rounded-lg
                                           border
                                           border-black/[0.08]
                                           bg-white
                                           px-3
                                           text-[12px]">
                                </div>

                                <div class="flex
                                       items-end">
                                    <button type="button"
                                        class="remove-item
                                           flex h-10 w-10
                                           items-center
                                           justify-center
                                           rounded-lg
                                           border
                                           border-red-100
                                           bg-red-50
                                           text-red-600">
                                        ×
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>


                {{-- NOTES --}}
                <section
                    class="rounded-2xl
                       border border-black/[0.07]
                       bg-white p-6">
                    <h2 class="text-[16px]
                           font-semibold">
                        Notes
                    </h2>

                    <textarea name="notes" rows="5" placeholder="Catatan invoice..."
                        class="mt-5 w-full
                           resize-none
                           rounded-xl
                           border border-black/[0.09]
                           bg-[#faf9f6]
                           p-3.5
                           text-[13px]">{{ old('notes') }}</textarea>
                </section>

            </div>


            {{-- SUMMARY --}}
            <aside>
                <section
                    class="sticky top-[96px]
                       rounded-2xl
                       bg-[#1d1d1b]
                       p-6 text-white">
                    <div
                        class="text-[11px]
                           font-semibold
                           uppercase
                           tracking-[.09em]
                           text-white/40">
                        Invoice Summary
                    </div>

                    <div class="mt-6 space-y-5">
                        <div
                            class="flex
                               items-center
                               justify-between">
                            <span class="text-[12px]
                                   text-white/45">
                                Subtotal
                            </span>

                            <span id="invoice-subtotal"
                                class="text-[14px]
                                   font-semibold">
                                Rp 0
                            </span>
                        </div>

                        <div>
                            <label
                                class="mb-2 block
                                   text-[12px]
                                   text-white/50">
                                Discount
                            </label>

                            <input type="number" id="discount-total" name="discount_total" min="0"
                                step="1"
                                value="{{ old('discount_total', 0) }}"
                                class="h-11 w-full
                                   rounded-xl
                                   border
                                   border-white/10
                                   bg-white/10
                                   px-3
                                   text-[13px]
                                   text-white
                                   outline-none">
                        </div>

                        <div>
                            <label
                                class="mb-2 block
                                   text-[12px]
                                   text-white/50">
                                Tax (%)
                            </label>

                            <input type="number" id="tax-percent" name="tax_percent" min="0" max="100"
                                step="0.01"
                                value="{{ old('tax_percent', $defaultTaxPercent) }}"
                                class="h-11 w-full
                                   rounded-xl
                                   border
                                   border-white/10
                                   bg-white/10
                                   px-3
                                   text-[13px]
                                   text-white
                                   outline-none">
                        </div>

                        <div
                            class="border-t
                               border-white/10
                               pt-5">
                            <div class="flex
                                   justify-between">
                                <span class="text-[13px]
                                       font-semibold">
                                    Total
                                </span>

                                <span id="invoice-total"
                                    class="text-[22px]
                                       font-semibold
                                       tracking-[-.04em]">
                                    Rp 0
                                </span>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="mt-7 h-11 w-full
                           rounded-xl
                           bg-white
                           text-[13px]
                           font-semibold
                           text-black">
                        Create Custom Invoice
                    </button>
                </section>
            </aside>
        </div>
    </form>


    <template id="invoice-item-template">
        <div
            class="invoice-item
               grid gap-3
               rounded-xl
               border border-black/[0.07]
               bg-[#faf9f6]
               p-4
               md:grid-cols-[120px_1fr_110px_160px_42px]">
            <div>
                <label class="mb-2 block
                       text-[11px]
                       font-medium">
                    Code
                </label>

                <input type="text" data-field="code" placeholder="ITEM"
                    class="h-10 w-full
                       rounded-lg
                       border border-black/[0.08]
                       bg-white px-3
                       text-[12px]">
            </div>

            <div>
                <label class="mb-2 block
                       text-[11px]
                       font-medium">
                    Description
                </label>

                <input type="text" data-field="description" required placeholder="Description"
                    class="h-10 w-full
                       rounded-lg
                       border border-black/[0.08]
                       bg-white px-3
                       text-[12px]">
            </div>

            <div>
                <label class="mb-2 block
                       text-[11px]
                       font-medium">
                    Qty
                </label>

                <input type="number" data-field="quantity" min="0.01" step="0.01" value="1" required
                    class="item-quantity
                       h-10 w-full
                       rounded-lg
                       border border-black/[0.08]
                       bg-white px-3
                       text-[12px]">
            </div>

            <div>
                <label class="mb-2 block
                       text-[11px]
                       font-medium">
                    Unit Price
                </label>

                <input type="number" data-field="unit_price" min="0" step="1" value="0" required
                    class="item-price
                       h-10 w-full
                       rounded-lg
                       border border-black/[0.08]
                       bg-white px-3
                       text-[12px]">
            </div>

            <div class="flex items-end">
                <button type="button"
                    class="remove-item
                       flex h-10 w-10
                       items-center
                       justify-center
                       rounded-lg
                       border border-red-100
                       bg-red-50
                       text-red-600">
                    ×
                </button>
            </div>
        </div>
    </template>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container =
                document.getElementById(
                    'invoice-items'
                );

            const addButton =
                document.getElementById(
                    'add-invoice-item'
                );

            const template =
                document.getElementById(
                    'invoice-item-template'
                );

            const subtotalElement =
                document.getElementById(
                    'invoice-subtotal'
                );

            const totalElement =
                document.getElementById(
                    'invoice-total'
                );

            const discountInput =
                document.getElementById(
                    'discount-total'
                );

            const taxInput =
                document.getElementById(
                    'tax-percent'
                );

            let itemIndex =
                container
                ?.querySelectorAll(
                    '.invoice-item'
                )
                .length ??
                0;

            const money = value => {
                return 'Rp ' +
                    new Intl.NumberFormat(
                        'id-ID'
                    ).format(
                        Math.round(
                            Number(value || 0)
                        )
                    );
            };

            const calculate = () => {
                let subtotal = 0;

                container
                    ?.querySelectorAll(
                        '.invoice-item'
                    )
                    .forEach(row => {
                        const qty =
                            Number(
                                row.querySelector(
                                    '.item-quantity'
                                )?.value ||
                                0
                            );

                        const price =
                            Number(
                                row.querySelector(
                                    '.item-price'
                                )?.value ||
                                0
                            );

                        subtotal +=
                            qty * price;
                    });

                const discount =
                    Math.max(
                        0,
                        Number(
                            discountInput?.value ||
                            0
                        )
                    );

                const taxable =
                    Math.max(
                        0,
                        subtotal - discount
                    );

                const taxPercent =
                    Math.max(
                        0,
                        Number(
                            taxInput?.value ||
                            0
                        )
                    );

                const tax =
                    taxable *
                    (
                        taxPercent /
                        100
                    );

                const total =
                    taxable + tax;

                if (subtotalElement) {
                    subtotalElement.textContent =
                        money(subtotal);
                }

                if (totalElement) {
                    totalElement.textContent =
                        money(total);
                }
            };

            const bindRow = row => {
                row
                    .querySelectorAll(
                        '.item-quantity, .item-price'
                    )
                    .forEach(input => {
                        input.addEventListener(
                            'input',
                            calculate
                        );
                    });

                row
                    .querySelector(
                        '.remove-item'
                    )
                    ?.addEventListener(
                        'click',
                        () => {
                            const rows =
                                container
                                .querySelectorAll(
                                    '.invoice-item'
                                );

                            if (
                                rows.length <= 1
                            ) {
                                return;
                            }

                            row.remove();
                            calculate();
                        }
                    );
            };

            container
                ?.querySelectorAll(
                    '.invoice-item'
                )
                .forEach(bindRow);

            addButton
                ?.addEventListener(
                    'click',
                    () => {
                        const fragment =
                            template
                            .content
                            .cloneNode(true);

                        const row =
                            fragment.querySelector(
                                '.invoice-item'
                            );

                        row
                            .querySelectorAll(
                                '[data-field]'
                            )
                            .forEach(input => {
                                input.name =
                                    `items[${itemIndex}][${input.dataset.field}]`;
                            });

                        itemIndex++;

                        bindRow(row);

                        container.appendChild(
                            fragment
                        );

                        calculate();
                    }
                );

            discountInput
                ?.addEventListener(
                    'input',
                    calculate
                );

            taxInput
                ?.addEventListener(
                    'input',
                    calculate
                );

            calculate();
        });
    </script>

@endsection
