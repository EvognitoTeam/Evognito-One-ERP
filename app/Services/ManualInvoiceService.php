<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\PlatformSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualInvoiceService
{
    public function create(
        Organization $organization,
        User $creator,
        array $data
    ): Invoice {
        $items =
            collect(
                $data['items']
                    ?? []
            )
            ->filter(
                fn($item) =>
                !empty(trim(
                        (string) (
                            $item['description']
                            ?? ''
                        )
                    ))
            )
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' =>
                'Minimal satu invoice item harus diisi.',
            ]);
        }

        $normalizedItems = [];

        $subtotal = 0;

        foreach (
            $items
            as $index => $item
        ) {
            $quantity =
                (float) (
                    $item['quantity']
                    ?? 1
                );

            $unitPrice =
                (float) (
                    $item['unit_price']
                    ?? 0
                );

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" =>
                    'Quantity harus lebih dari 0.',
                ]);
            }

            if ($unitPrice < 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.unit_price" =>
                    'Harga tidak boleh negatif.',
                ]);
            }

            $amount =
                round(
                    $quantity
                        * $unitPrice,
                    2
                );

            $subtotal +=
                $amount;

            $normalizedItems[] = [
                'module_id' =>
                null,

                'type' =>
                'custom',

                'code' =>
                !empty($item['code'])
                    ? strtoupper(
                        trim(
                            $item['code']
                        )
                    )
                    : 'CUSTOM-'
                    . str_pad(
                        (string) (
                            $index + 1
                        ),
                        2,
                        '0',
                        STR_PAD_LEFT
                    ),

                'description' =>
                trim(
                    $item['description']
                ),

                'quantity' =>
                $quantity,

                'unit_price' =>
                $unitPrice,

                'amount' =>
                $amount,

                'metadata' => [
                    'source' =>
                    'manual',
                ],
            ];
        }

        $discountTotal =
            max(
                0,
                (float) (
                    $data['discount_total']
                    ?? 0
                )
            );

        if (
            $discountTotal
            > $subtotal
        ) {
            throw ValidationException::withMessages([
                'discount_total' =>
                'Discount tidak boleh melebihi subtotal.',
            ]);
        }

        $taxPercent =
            max(
                0,
                min(
                    100,
                    (float) (
                        $data['tax_percent']
                        ?? 0
                    )
                )
            );

        $taxable =
            max(
                0,
                $subtotal
                    - $discountTotal
            );

        $taxTotal =
            round(
                $taxable
                    * (
                        $taxPercent
                        / 100
                    ),
                2
            );

        $total =
            round(
                $taxable
                    + $taxTotal,
                2
            );

        $issueNow =
            (bool) (
                $data['issue_now']
                ?? true
            );

        $dueAt = null;

        if ($issueNow) {
            if (
                !empty($data['due_at'])
            ) {
                $dueAt =
                    Carbon::parse(
                        $data['due_at']
                    );
            } else {
                $dueDays =
                    max(
                        0,
                        (int) PlatformSetting::getValue(
                            'billing.invoice_due_days',
                            7
                        )
                    );

                $dueAt =
                    now()
                    ->addDays(
                        $dueDays
                    );
            }
        }

        return DB::transaction(
            function () use (
                $organization,
                $creator,
                $data,
                $issueNow,
                $dueAt,
                $subtotal,
                $discountTotal,
                $taxPercent,
                $taxTotal,
                $total,
                $normalizedItems
            ) {
                $invoice =
                    Invoice::create([
                        'organization_id' =>
                        $organization->id,

                        'organization_subscription_id' =>
                        null,

                        'source' =>
                        'manual',

                        'title' =>
                        trim(
                            $data['title']
                        ),

                        'period_start' =>
                        !empty($data['period_start'])
                            ? $data['period_start']
                            : null,

                        'period_end' =>
                        !empty($data['period_end'])
                            ? $data['period_end']
                            : null,

                        'issued_at' =>
                        $issueNow
                            ? now()
                            : null,

                        'due_at' =>
                        $dueAt,

                        'currency' =>
                        'IDR',

                        'status' =>
                        $issueNow
                            ? 'issued'
                            : 'draft',

                        'subtotal' =>
                        $subtotal,

                        /*
                         * Additional fee tidak disimpan
                         * menjadi JSON.
                         *
                         * Semua additional charge
                         * menjadi invoice item.
                         */
                        'additional_total' =>
                        0,

                        'discount_total' =>
                        $discountTotal,

                        'tax_total' =>
                        $taxTotal,

                        'total' =>
                        $total,

                        'notes' =>
                        $data['notes']
                            ?? null,

                        'metadata' => [
                            'source' =>
                            'manual',

                            'tax_percent' =>
                            $taxPercent,

                            /*
                             * Snapshot supaya histori
                             * invoice tetap konsisten
                             * jika organization berubah.
                             */
                            'organization_snapshot' => [
                                'name' =>
                                $organization
                                    ->name,

                                'legal_name' =>
                                $organization
                                    ->legal_name,

                                'email' =>
                                $organization
                                    ->email,

                                'phone' =>
                                $organization
                                    ->phone,

                                'address' =>
                                $organization
                                    ->address,
                            ],

                            'created_at' =>
                            now()
                                ->toIso8601String(),
                        ],

                        'created_by' =>
                        $creator->id,
                    ]);

                foreach (
                    $normalizedItems
                    as $item
                ) {
                    $invoice
                        ->items()
                        ->create(
                            $item
                        );
                }

                return $invoice;
            }
        );
    }
}
