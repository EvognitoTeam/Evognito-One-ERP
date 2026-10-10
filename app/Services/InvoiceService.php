<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\OrganizationSubscription;
use App\Models\PlatformSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        protected BillingCalculator $calculator
    ) {}

    public function generate(
        OrganizationSubscription $subscription,
        bool $issueNow = true
    ): Invoice {
        $subscription->loadMissing([
            'organization',
            'plan',
        ]);

        if (
            in_array(
                $subscription->status,
                [
                    'canceled',
                    'suspended',
                    'expired',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'subscription' =>
                'Subscription ini tidak dapat dibuatkan invoice.',
            ]);
        }

        if (
            $subscription->status === 'trialing'
            && $subscription->trial_ends_at
            && $subscription->trial_ends_at->isFuture()
        ) {
            throw ValidationException::withMessages([
                'subscription' =>
                'Subscription masih berada dalam trial period.',
            ]);
        }

        $periodStart =
            $subscription
            ->current_period_start
            ?->copy()
            ?? $subscription
            ->starts_at
            ?->copy()
            ?? now();

        $periodEnd =
            $subscription
            ->current_period_end
            ?->copy();

        if (!$periodEnd) {
            $periodEnd =
                $subscription
                ->plan
                ->billing_interval
                === 'yearly'
                ? $periodStart
                ->copy()
                ->addYear()
                : $periodStart
                ->copy()
                ->addMonth();
        }

        $duplicate =
            Invoice::query()
            ->where(
                'organization_subscription_id',
                $subscription->id
            )
            ->whereDate(
                'period_start',
                $periodStart
                    ->toDateString()
            )
            ->whereDate(
                'period_end',
                $periodEnd
                    ->toDateString()
            )
            ->where(
                'status',
                '!=',
                'canceled'
            )
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'subscription' =>
                'Invoice untuk periode ini sudah tersedia.',
            ]);
        }

        $calculation =
            $this->calculator
            ->calculate(
                $subscription,
                $periodStart,
                $periodEnd
            );

        $dueDays =
            max(
                0,
                (int) PlatformSetting::getValue(
                    'billing.invoice_due_days',
                    7
                )
            );

        return DB::transaction(
            function () use (
                $subscription,
                $issueNow,
                $periodStart,
                $periodEnd,
                $calculation,
                $dueDays
            ) {
                $invoice =
                    Invoice::create([
                        'organization_id' =>
                        $subscription
                            ->organization_id,

                        'organization_subscription_id' =>
                        $subscription->id,

                        'source' =>
                        'subscription',

                        'title' =>
                        $subscription
                            ->plan
                            ?->name
                            . ' Subscription',

                        'period_start' =>
                        $periodStart
                            ->toDateString(),

                        'period_end' =>
                        $periodEnd
                            ->toDateString(),

                        'issued_at' =>
                        $issueNow
                            ? now()
                            : null,

                        'due_at' =>
                        $issueNow
                            ? now()->addDays(
                                $dueDays
                            )
                            : null,

                        'currency' =>
                        'IDR',

                        'status' =>
                        $issueNow
                            ? 'issued'
                            : 'draft',

                        'subtotal' =>
                        $calculation['subtotal'],

                        'additional_total' =>
                        $calculation['additional_total'],

                        'discount_total' =>
                        $calculation['discount_total'],

                        'tax_total' =>
                        $calculation['tax_total'],

                        'total' =>
                        $calculation['total'],

                        'metadata' => [
                            'plan_id' =>
                            $subscription
                                ->subscription_plan_id,

                            'generated_at' =>
                            now()
                                ->toIso8601String(),
                        ],
                    ]);

                foreach (
                    $calculation['items']
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

    public function issue(
        Invoice $invoice
    ): Invoice {
        if ($invoice->status !== 'draft') {
            return $invoice;
        }

        $dueDays =
            max(
                0,
                (int) PlatformSetting::getValue(
                    'billing.invoice_due_days',
                    7
                )
            );

        $invoice->update([
            'status' =>
            'issued',

            'issued_at' =>
            now(),

            'due_at' =>
            now()->addDays(
                $dueDays
            ),
        ]);

        return $invoice->refresh();
    }

    public function cancel(
        Invoice $invoice
    ): Invoice {
        if ($invoice->status === 'paid') {
            throw ValidationException::withMessages([
                'invoice' =>
                'Invoice yang sudah lunas tidak dapat dibatalkan.',
            ]);
        }

        $invoice->update([
            'status' =>
            'canceled',
        ]);

        return $invoice->refresh();
    }

    public function syncOverdue(): void
    {
        Invoice::query()
            ->whereIn(
                'status',
                [
                    'issued',
                    'partial',
                ]
            )
            ->whereNotNull(
                'due_at'
            )
            ->where(
                'due_at',
                '<',
                now()
            )
            ->update([
                'status' =>
                'overdue',
            ]);
    }
}
